<?php
/**
 * Storage Library for CodeIgniter 4
 *
 * This library provides a unified interface to interact with different
 * object storage services for uploads and deletions. It only initializes
 * the client for the currently active storage service.
 *
 */

namespace App\Libraries;

// Include the AWS SDK autoloader
require APPPATH . 'ThirdParty/aws-sdk/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Credentials\Credentials;

class Storage
{
    /** @var S3Client|null The AWS S3 client instance. */
    private ?S3Client $s3Client = null;

    /** @var S3Client|null The Cloudflare R2 client instance. */
    private ?S3Client $cloudflareClient = null;

    /** @var S3Client|null The Backblaze B2 client instance. */
    private ?S3Client $backblazeClient = null;

    /** @var array Holds bucket information for each service. */
    protected array $buckets = [];

    /** @var string The key of the currently active storage service ('aws_s3', 'cloudflare_r2', or 'backblaze_b2'). */
    protected string $activeStorage;

    /**
     * Constructor: Initializes the client only for the active storage service.
     *
     * @param object $settings A flat object containing all necessary credentials and configuration.
     */
    public function __construct(object $settings)
    {
        // Set the active storage driver, defaulting to 'local' if not specified.
        $this->activeStorage = $settings->storage ?? 'local';

        // Populate bucket info for all services, but only initialize the active client.
        $this->buckets['aws_s3'] = $settings->aws_bucket ?? '';
        $this->buckets['cloudflare_r2'] = $settings->r2_bucket ?? '';
        $this->buckets['backblaze_b2'] = $settings->b2_bucket ?? '';

        // Initialize only the client for the active storage to save resources.
        switch ($this->activeStorage) {
            case 'aws_s3':
                if (!empty($settings->aws_key) && !empty($settings->aws_secret)) {
                    $this->s3Client = $this->buildS3Client($settings);
                }
                break;

            case 'cloudflare_r2':
                if (!empty($settings->r2_key) && !empty($settings->r2_secret) && !empty($settings->r2_endpoint_url)) {
                    $this->cloudflareClient = $this->buildCloudflareClient($settings);
                }
                break;

            case 'backblaze_b2':
                if (!empty($settings->b2_key) && !empty($settings->b2_secret) && !empty($settings->b2_endpoint_url)) {
                    $this->backblazeClient = $this->buildBackblazeClient($settings);
                }
                break;
        }
    }

    /**
     * Builds the AWS S3 client.
     * @param object $settings
     * @return S3Client
     */
    private function buildS3Client(object $settings): S3Client
    {
        $credentials = new Credentials($settings->aws_key, $settings->aws_secret);
        return new S3Client([
            'version' => 'latest',
            'region' => $settings->aws_region,
            'credentials' => $credentials,
        ]);
    }

    /**
     * Builds the Cloudflare R2 S3-compatible client.
     * @param object $settings
     * @return S3Client
     */
    private function buildCloudflareClient(object $settings): S3Client
    {
        $credentials = new Credentials($settings->r2_key, $settings->r2_secret);

        $endpoint = $settings->r2_endpoint_url;
        // Ensure the endpoint has a protocol for robustness.
        if (strpos($endpoint, 'http') !== 0) {
            $endpoint = 'https://' . $endpoint;
        }

        return new S3Client([
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => $endpoint,
            'credentials' => $credentials,
            'signature_version' => 'v4',
        ]);
    }

    /**
     * Builds the Backblaze B2 S3-compatible client.
     * @param object $settings
     * @return S3Client
     */
    private function buildBackblazeClient(object $settings): S3Client
    {
        $credentials = new Credentials($settings->b2_key, $settings->b2_secret);

        $host = preg_replace('#^https?://#', '', $settings->b2_endpoint_url);
        $endpointParts = explode('.', $host);
        $region = $endpointParts[1] ?? 'us-west-002';

        $endpoint = $settings->b2_endpoint_url;
        // Ensure the endpoint has a protocol for robustness.
        if (strpos($endpoint, 'http') !== 0) {
            $endpoint = 'https://' . $endpoint;
        }

        return new S3Client([
            'version' => 'latest',
            'region' => $region,
            'endpoint' => $endpoint,
            'credentials' => $credentials,
            'signature_version' => 'v4',
        ]);
    }

    /**
     * Uploads a file to the active storage service.
     * @param string $key
     * @param string $tempPath
     * @return bool
     */
    public function putObject(string $key, string $tempPath): bool
    {
        if (!file_exists($tempPath)) {
            log_message('error', 'Storage Upload Error: Source file does not exist at ' . $tempPath);
            return false;
        }

        $client = $this->getClient($this->activeStorage);
        $bucket = $this->buckets[$this->activeStorage] ?? null;

        if (!$client || !$bucket) {
            log_message('error', 'Storage Upload Error: Active storage client or bucket not configured for ' . $this->activeStorage);
            return false;
        }

        try {
            $file = fopen($tempPath, 'r');
            if ($file === false) {
                log_message('error', 'Storage Upload Error: Could not open file for reading at ' . $tempPath);
                return false;
            }

            $params = [
                'Bucket' => $bucket,
                'Key' => $key,
                'Body' => $file,
            ];

            if ($this->activeStorage === 'aws_s3') {
                $params['ACL'] = 'public-read';
            }

            $client->putObject($params);

            if (is_resource($file)) {
                fclose($file);
            }

            return true;
        } catch (S3Exception $e) {
            log_message('error', 'Storage Upload Error (' . $this->activeStorage . '): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes an object from a specified storage bucket.
     *
     * @param string $key The key of the object to delete.
     * @param string $storage The identifier for the storage configuration (e.g., 'aws_s3').
     * @return bool           True on success, false on failure.
     */
    public function deleteObject(string $key, string $storage): bool
    {
        $client = $this->getClient($storage);
        $bucket = $this->buckets[$storage] ?? null;

        if (!$client) {
            log_message('error', 'Storage Deletion Error: Client for storage "' . $storage . '" is not initialized. The active storage is "' . $this->activeStorage . '".');
            return false;
        }

        if (!$bucket || empty($key)) {
            log_message('error', 'Storage Deletion Error: Bucket not configured or key is empty for storage "' . $storage . '".');
            return false;
        }

        try {
            $client->deleteObject([
                'Bucket' => $bucket,
                'Key'    => $key,
            ]);
            return true;
        } catch (S3Exception $e) {
            log_message('error', 'Storage Deletion Error (' . $storage . '): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Downloads an object from a specified storage service to a local file.
     *
     * @param string $key The key of the object to download.
     * @param string $destinationPath The local path to save the file to.
     * @param string $storage The identifier for the storage configuration.
     * @return bool Returns true on success, false on failure.
     */
    public function downloadFile(string $key, string $destinationPath, string $storage): bool
    {
        $client = $this->getClient($storage);
        $bucket = $this->buckets[$storage] ?? null;

        if (!$client) {
            log_message('error', 'Storage Download Error: Client for storage "' . $storage . '" is not initialized. The active storage is "' . $this->activeStorage . '".');
            return false;
        }

        if (!$bucket || empty($key)) {
            log_message('error', 'Storage Download Error: Bucket not configured or key is empty for storage "' . $storage . '".');
            return false;
        }

        $destinationDir = dirname($destinationPath);
        if (!is_dir($destinationDir) || !is_writable($destinationDir)) {
            log_message('error', 'Storage Download Error: Destination directory is not writable: ' . $destinationDir);
            return false;
        }

        try {
            $client->getObject([
                'Bucket' => $bucket,
                'Key' => $key,
                'SaveAs' => $destinationPath,
            ]);
            return true;
        } catch (S3Exception $e) {
            log_message('error', 'Storage Download Error (' . $storage . '): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves the S3 client for a specific storage service.
     * @param string $storageType The identifier of the storage service.
     * @return S3Client|null
     */
    private function getClient(string $storageType): ?S3Client
    {
        return match ($storageType) {
            'aws_s3' => $this->s3Client,
            'cloudflare_r2' => $this->cloudflareClient,
            'backblaze_b2' => $this->backblazeClient,
            default => null,
        };
    }
}