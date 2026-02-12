-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20250519.4c4fa606a0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 26, 2025 at 12:58 PM
-- Server version: 8.0.40
-- PHP Version: 8.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `install_modesy`
--

-- --------------------------------------------------------

--
-- Table structure for table `abuse_reports`
--

CREATE TABLE `abuse_reports` (
  `id` int NOT NULL,
  `item_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'product',
  `item_id` int DEFAULT NULL,
  `report_user_id` int DEFAULT NULL,
  `description` varchar(10000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ad_spaces`
--

CREATE TABLE `ad_spaces` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT '1',
  `ad_space` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ad_code_desktop` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `desktop_width` int DEFAULT NULL,
  `desktop_height` int DEFAULT NULL,
  `ad_code_mobile` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `mobile_width` int DEFAULT NULL,
  `mobile_height` int DEFAULT NULL,
  `storage` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `affiliate_earnings`
--

CREATE TABLE `affiliate_earnings` (
  `id` int NOT NULL,
  `referrer_id` int DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `seller_id` int DEFAULT NULL,
  `commission_rate` tinyint DEFAULT NULL,
  `earned_amount` decimal(12,2) DEFAULT '0.00',
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'USD',
  `exchange_rate` double DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `affiliate_links`
--

CREATE TABLE `affiliate_links` (
  `id` int NOT NULL,
  `referrer_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `seller_id` int DEFAULT NULL,
  `lang_id` int DEFAULT NULL,
  `link_short` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_transfers`
--

CREATE TABLE `bank_transfers` (
  `id` int NOT NULL,
  `report_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'order',
  `report_item_id` int DEFAULT NULL,
  `order_number` bigint DEFAULT NULL,
  `payment_note` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `receipt_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `storage` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `user_id` int DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `ip_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` int NOT NULL,
  `lang_id` tinyint DEFAULT '1',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category_order` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_comments`
--

CREATE TABLE `blog_comments` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `comment` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_images`
--

CREATE TABLE `blog_images` (
  `id` int NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_path_thumb` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int NOT NULL,
  `lang_id` tinyint DEFAULT '1',
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `slug` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `summary` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `keywords` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `image_default` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_small` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_id` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_tags`
--

CREATE TABLE `blog_tags` (
  `id` int NOT NULL,
  `post_id` int DEFAULT NULL,
  `tag` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tag_slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `show_on_slider` tinyint(1) DEFAULT '0',
  `storage` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brand_category`
--

CREATE TABLE `brand_category` (
  `id` int NOT NULL,
  `brand_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brand_lang`
--

CREATE TABLE `brand_lang` (
  `id` int NOT NULL,
  `brand_id` int DEFAULT NULL,
  `lang_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `session_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `currency_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `currency_code_base` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `exchange_rate` double DEFAULT '1',
  `shipping_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `shipping_cost` decimal(12,2) DEFAULT '0.00',
  `shipping_cost_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `coupon_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_method` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `location_country_id` int DEFAULT NULL,
  `location_state_id` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int NOT NULL,
  `cart_id` int DEFAULT NULL,
  `item_hash` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `seller_id` int DEFAULT NULL,
  `product_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'physical',
  `listing_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `unit_price` decimal(12,2) DEFAULT '0.00',
  `unit_price_base` decimal(12,2) DEFAULT '0.00',
  `total_price` decimal(12,2) DEFAULT '0.00',
  `product_vat` decimal(12,2) DEFAULT '0.00',
  `product_vat_rate` double DEFAULT NULL,
  `product_image_id` int DEFAULT NULL,
  `product_image_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `purchase_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quote_request_id` int DEFAULT '0',
  `variant_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `extra_options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `extra_options_hash` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `product_options_snapshot` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `product_options_summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_stock_available` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `parent_id` int DEFAULT '0',
  `category_order` mediumint DEFAULT '0',
  `featured_order` mediumint DEFAULT '1',
  `homepage_order` mediumint DEFAULT '5',
  `status` tinyint(1) DEFAULT '1',
  `is_featured` tinyint(1) DEFAULT '0',
  `show_on_main_menu` tinyint(1) DEFAULT '1',
  `show_image_on_main_menu` tinyint(1) DEFAULT '0',
  `show_products_on_index` tinyint(1) DEFAULT '0',
  `show_subcategory_products` tinyint(1) DEFAULT '0',
  `storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `show_description` tinyint(1) DEFAULT '0',
  `is_commission_set` tinyint(1) DEFAULT '0',
  `commission_rate` decimal(5,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category_lang`
--

CREATE TABLE `category_lang` (
  `id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `lang_id` tinyint DEFAULT '1',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `meta_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `meta_keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category_paths`
--

CREATE TABLE `category_paths` (
  `id` int NOT NULL,
  `ancestor_id` int DEFAULT NULL,
  `descendant_id` int DEFAULT NULL,
  `depth` smallint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `id` int NOT NULL,
  `sender_id` int DEFAULT NULL,
  `receiver_id` int DEFAULT NULL,
  `subject` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_id` int DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int NOT NULL,
  `chat_id` int DEFAULT NULL,
  `sender_id` int DEFAULT NULL,
  `receiver_id` int DEFAULT NULL,
  `message` varchar(10000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_user_id` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checkouts`
--

CREATE TABLE `checkouts` (
  `id` bigint NOT NULL,
  `cart_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `session_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `checkout_token` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `checkout_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'product',
  `payment_method` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT NULL,
  `shipping_cost` decimal(12,2) DEFAULT '0.00',
  `grand_total` decimal(12,2) DEFAULT NULL,
  `grand_total_base` decimal(12,2) DEFAULT NULL,
  `cart_totals_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `currency_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `currency_code_base` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `exchange_rate` double DEFAULT '1',
  `shipping_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `shipping_cost_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `coupon_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `service_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `service_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `service_tax_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `has_physical_product` tinyint(1) DEFAULT '0',
  `has_digital_product` tinyint(1) DEFAULT '0',
  `transaction_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `payment_url` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checkout_items`
--

CREATE TABLE `checkout_items` (
  `id` bigint NOT NULL,
  `checkout_id` bigint DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `seller_id` int DEFAULT NULL,
  `product_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'physical',
  `listing_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `unit_price` decimal(12,2) DEFAULT '0.00',
  `unit_price_base` decimal(12,2) DEFAULT NULL,
  `total_price` decimal(12,2) DEFAULT '0.00',
  `product_vat` decimal(12,2) DEFAULT '0.00',
  `product_vat_rate` double DEFAULT NULL,
  `product_image_id` int DEFAULT NULL,
  `product_image_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `quote_request_id` int DEFAULT '0',
  `product_options_snapshot` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `product_options_summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `extra_options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `product_commission_rate` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

CREATE TABLE `ci_sessions` (
  `id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `parent_id` int DEFAULT '0',
  `product_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `comment` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int NOT NULL,
  `seller_id` int DEFAULT NULL,
  `coupon_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `discount_rate` smallint DEFAULT NULL,
  `coupon_count` int DEFAULT NULL,
  `minimum_order_amount` decimal(12,2) DEFAULT '0.00',
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `usage_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'single',
  `category_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `expiry_date` timestamp NULL DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons_used`
--

CREATE TABLE `coupons_used` (
  `id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `coupon_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_products`
--

CREATE TABLE `coupon_products` (
  `id` int NOT NULL,
  `coupon_id` int NOT NULL,
  `product_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` int NOT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `symbol` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `currency_format` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'us',
  `symbol_direction` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'left',
  `space_money_symbol` tinyint(1) DEFAULT '0',
  `exchange_rate` double DEFAULT '1',
  `status` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields`
--

CREATE TABLE `custom_fields` (
  `id` int NOT NULL,
  `field_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `field_order` int DEFAULT '1',
  `is_product_filter` tinyint(1) DEFAULT '0',
  `product_filter_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sort_options` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'alphabetically',
  `where_to_display` tinyint DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields_category`
--

CREATE TABLE `custom_fields_category` (
  `id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `field_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields_options`
--

CREATE TABLE `custom_fields_options` (
  `id` int NOT NULL,
  `field_id` int DEFAULT NULL,
  `option_key` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields_product`
--

CREATE TABLE `custom_fields_product` (
  `id` int NOT NULL,
  `field_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `product_filter_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `field_value` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `selected_option_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_field_lang`
--

CREATE TABLE `custom_field_lang` (
  `id` int NOT NULL,
  `field_id` int DEFAULT NULL,
  `lang_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_field_option_lang`
--

CREATE TABLE `custom_field_option_lang` (
  `id` int NOT NULL,
  `option_id` int DEFAULT NULL,
  `lang_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `digital_files`
--

CREATE TABLE `digital_files` (
  `id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `digital_sales`
--

CREATE TABLE `digital_sales` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `seller_id` int NOT NULL,
  `buyer_id` int NOT NULL,
  `license_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `purchase_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'USD',
  `price` decimal(12,2) DEFAULT '0.00',
  `purchase_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `earnings`
--

CREATE TABLE `earnings` (
  `id` int NOT NULL,
  `order_number` bigint DEFAULT NULL,
  `order_product_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `sale_amount` decimal(12,2) DEFAULT '0.00',
  `vat_rate` double DEFAULT NULL,
  `vat_amount` decimal(12,2) DEFAULT '0.00',
  `commission_rate` tinyint DEFAULT NULL,
  `commission` decimal(12,2) DEFAULT '0.00',
  `coupon_discount` decimal(12,2) DEFAULT '0.00',
  `shipping_cost` decimal(12,2) DEFAULT '0.00',
  `earned_amount` decimal(12,2) DEFAULT '0.00',
  `affiliate_commission` decimal(12,2) DEFAULT '0.00',
  `affiliate_commission_rate` double DEFAULT '0',
  `affiliate_discount` decimal(12,2) DEFAULT '0.00',
  `affiliate_discount_rate` double DEFAULT '0',
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'USD',
  `exchange_rate` double DEFAULT '1',
  `is_refunded` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int NOT NULL,
  `email_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email_subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `email_priority` smallint DEFAULT '2',
  `template_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `followers`
--

CREATE TABLE `followers` (
  `id` int NOT NULL,
  `following_id` int DEFAULT NULL,
  `follower_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fonts`
--

CREATE TABLE `fonts` (
  `id` int NOT NULL,
  `font_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `font_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `font_url` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `font_family` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `font_source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'google',
  `has_local_file` tinyint(1) DEFAULT '0',
  `is_default` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fonts`
--

INSERT INTO `fonts` (`id`, `font_name`, `font_key`, `font_url`, `font_family`, `font_source`, `has_local_file`, `is_default`) VALUES
(1, 'Arial', 'arial', NULL, 'font-family: Arial, Helvetica, sans-serif', 'local', 0, 1),
(2, 'Arvo', 'arvo', '<link href=\"https://fonts.googleapis.com/css?family=Arvo:400,700&display=swap\" rel=\"stylesheet\">\r\n', 'font-family: \"Arvo\", Helvetica, sans-serif', 'google', 0, 0),
(3, 'Averia Libre', 'averia-libre', '<link href=\"https://fonts.googleapis.com/css?family=Averia+Libre:300,400,700&display=swap\" rel=\"stylesheet\">\r\n', 'font-family: \"Averia Libre\", Helvetica, sans-serif', 'google', 0, 0),
(4, 'Bitter', 'bitter', '<link href=\"https://fonts.googleapis.com/css?family=Bitter:400,400i,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Bitter\", Helvetica, sans-serif', 'google', 0, 0),
(5, 'Cabin', 'cabin', '<link href=\"https://fonts.googleapis.com/css?family=Cabin:400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Cabin\", Helvetica, sans-serif', 'google', 0, 0),
(6, 'Cherry Swash', 'cherry-swash', '<link href=\"https://fonts.googleapis.com/css?family=Cherry+Swash:400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Cherry Swash\", Helvetica, sans-serif', 'google', 0, 0),
(7, 'Encode Sans', 'encode-sans', '<link href=\"https://fonts.googleapis.com/css?family=Encode+Sans:300,400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Encode Sans\", Helvetica, sans-serif', 'google', 0, 0),
(8, 'Helvetica', 'helvetica', NULL, 'font-family: Helvetica, sans-serif', 'local', 0, 1),
(9, 'Hind', 'hind', '<link href=\"https://fonts.googleapis.com/css?family=Hind:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">', 'font-family: \"Hind\", Helvetica, sans-serif', 'google', 0, 0),
(10, 'Inter', 'inter', NULL, 'font-family: \"Inter\", sans-serif;', 'local', 1, 0),
(11, 'Josefin Sans', 'josefin-sans', '<link href=\"https://fonts.googleapis.com/css?family=Josefin+Sans:300,400,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Josefin Sans\", Helvetica, sans-serif', 'google', 0, 0),
(12, 'Kalam', 'kalam', '<link href=\"https://fonts.googleapis.com/css?family=Kalam:300,400,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Kalam\", Helvetica, sans-serif', 'google', 0, 0),
(13, 'Khula', 'khula', '<link href=\"https://fonts.googleapis.com/css?family=Khula:300,400,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Khula\", Helvetica, sans-serif', 'google', 0, 0),
(14, 'Lato', 'lato', '<link href=\"https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">', 'font-family: \"Lato\", Helvetica, sans-serif', 'google', 0, 0),
(15, 'Libre Baskerville', 'libre-baskerville', '<link href=\"https://fonts.googleapis.com/css?family=Libre+Baskerville:400,400i&display=swap&subset=latin-ext\" rel=\"stylesheet\"> ', 'font-family: \"Libre Baskerville\", Helvetica, sans-serif', 'google', 0, 0),
(16, 'Lora', 'lora', '<link href=\"https://fonts.googleapis.com/css?family=Lora:400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Lora\", Helvetica, sans-serif', 'google', 0, 0),
(17, 'Merriweather', 'merriweather', '<link href=\"https://fonts.googleapis.com/css?family=Merriweather:300,400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Merriweather\", Helvetica, sans-serif', 'google', 0, 0),
(18, 'Montserrat', 'montserrat', '<link href=\"https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Montserrat\", Helvetica, sans-serif', 'google', 0, 0),
(19, 'Mukta', 'mukta', '<link href=\"https://fonts.googleapis.com/css?family=Mukta:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Mukta\", Helvetica, sans-serif', 'google', 0, 0),
(20, 'Nunito', 'nunito', '<link href=\"https://fonts.googleapis.com/css?family=Nunito:300,400,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Nunito\", Helvetica, sans-serif', 'google', 0, 0),
(21, 'Open Sans', 'open-sans', '<link href=\"https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&display=swap\" rel=\"stylesheet\">', 'font-family: \"Open Sans\", Helvetica, sans-serif', 'local', 1, 0),
(22, 'Oswald', 'oswald', '<link href=\"https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Oswald\", Helvetica, sans-serif', 'google', 0, 0),
(23, 'Oxygen', 'oxygen', '<link href=\"https://fonts.googleapis.com/css?family=Oxygen:300,400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Oxygen\", Helvetica, sans-serif', 'google', 0, 0),
(24, 'Poppins', 'poppins', '<link href=\"https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Poppins\", Helvetica, sans-serif', 'local', 1, 0),
(25, 'PT Sans', 'pt-sans', '<link href=\"https://fonts.googleapis.com/css?family=PT+Sans:400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"PT Sans\", Helvetica, sans-serif', 'google', 0, 0),
(26, 'Raleway', 'raleway', '<link href=\"https://fonts.googleapis.com/css?family=Raleway:300,400,500,600,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Raleway\", Helvetica, sans-serif', 'google', 0, 0),
(27, 'Roboto', 'roboto', '<link href=\"https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Roboto\", Helvetica, sans-serif', 'google', 0, 0),
(28, 'Roboto Condensed', 'roboto-condensed', '<link href=\"https://fonts.googleapis.com/css?family=Roboto+Condensed:300,400,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Roboto Condensed\", Helvetica, sans-serif', 'google', 0, 0),
(29, 'Roboto Slab', 'roboto-slab', '<link href=\"https://fonts.googleapis.com/css?family=Roboto+Slab:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Roboto Slab\", Helvetica, sans-serif', 'google', 0, 0),
(30, 'Rokkitt', 'rokkitt', '<link href=\"https://fonts.googleapis.com/css?family=Rokkitt:300,400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Rokkitt\", Helvetica, sans-serif', 'google', 0, 0),
(31, 'Signika', 'signika', '<link href=\"https://fonts.googleapis.com/css2?family=Signika:wght@300;400;600;700&display=swap\" rel=\"stylesheet\">', 'font-family: \'Signika\', sans-serif;', 'google', 0, 0),
(32, 'Source Sans Pro', 'source-sans-pro', '<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Source Sans Pro\", Helvetica, sans-serif', 'google', 0, 0),
(33, 'Tajawal', 'tajawal', '<link href=\"https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap\" rel=\"stylesheet\">', 'font-family: \'Tajawal\', sans-serif;', 'google', 0, 0),
(34, 'Titillium Web', 'titillium-web', '<link href=\"https://fonts.googleapis.com/css?family=Titillium+Web:300,400,600,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">', 'font-family: \"Titillium Web\", Helvetica, sans-serif', 'google', 0, 0),
(35, 'Ubuntu', 'ubuntu', '<link href=\"https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext\" rel=\"stylesheet\">', 'font-family: \"Ubuntu\", Helvetica, sans-serif', 'google', 0, 0),
(36, 'Verdana', 'verdana', NULL, 'font-family: Verdana, Helvetica, sans-serif', 'local', 0, 1),
(37, 'Work Sans', 'work-sans', '<link href=\"https://fonts.googleapis.com/css?family=Work+Sans:300,400,500,600&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\"> ', 'font-family: \"Work Sans\", Helvetica, sans-serif', 'google', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `general_settings`
--

CREATE TABLE `general_settings` (
  `id` int NOT NULL,
  `physical_products_system` tinyint(1) DEFAULT '1',
  `digital_products_system` tinyint(1) DEFAULT '1',
  `marketplace_system` tinyint(1) DEFAULT '1',
  `classified_ads_system` tinyint(1) DEFAULT '1',
  `bidding_system` tinyint(1) DEFAULT '1',
  `selling_license_keys_system` tinyint(1) DEFAULT '1',
  `multi_vendor_system` tinyint(1) DEFAULT '1',
  `membership_plans_system` tinyint(1) DEFAULT '0',
  `site_lang` tinyint DEFAULT '1',
  `timezone` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'America/New_York',
  `application_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `selected_navigation` tinyint DEFAULT '1',
  `fea_categories_design` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'round_boxes',
  `menu_limit` tinyint DEFAULT '8',
  `turnstile_status` tinyint(1) DEFAULT '0',
  `turnstile_site_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `turnstile_secret_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `custom_header_codes` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `custom_footer_codes` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `email_verification` tinyint(1) DEFAULT '0',
  `storage_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `affiliate_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `email_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `newsletter_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `watermark_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `facebook_app_id` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `facebook_app_secret` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `google_client_id` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `google_client_secret` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vk_app_id` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vk_secure_key` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `google_analytics` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `promoted_products` tinyint(1) DEFAULT '1',
  `multilingual_system` tinyint(1) DEFAULT '1',
  `product_comments` tinyint(1) DEFAULT '1',
  `comment_approval_system` tinyint(1) DEFAULT '0',
  `reviews` tinyint(1) DEFAULT '1',
  `blog_comments` tinyint(1) DEFAULT '1',
  `slider_status` tinyint DEFAULT '1',
  `slider_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'full_width',
  `slider_effect` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'fade',
  `featured_categories` tinyint(1) DEFAULT '1',
  `index_promoted_products` tinyint(1) DEFAULT '1',
  `index_promoted_products_count` smallint DEFAULT '12',
  `index_latest_products` tinyint(1) DEFAULT '1',
  `index_latest_products_count` smallint DEFAULT '12',
  `index_blog_slider` tinyint(1) DEFAULT '1',
  `index_products_per_row` mediumint DEFAULT '6',
  `product_link_structure` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'slug-id',
  `site_color` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'default',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `favicon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `facebook_comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `facebook_comment_status` tinyint(1) DEFAULT '0',
  `cache_system` tinyint(1) DEFAULT '0',
  `static_cache_system` tinyint(1) DEFAULT '1',
  `category_cache_system` tinyint(1) DEFAULT '0',
  `refresh_cache_database_changes` tinyint(1) DEFAULT '0',
  `cache_refresh_time` int DEFAULT '1800',
  `rss_system` tinyint(1) DEFAULT '1',
  `approve_before_publishing` tinyint(1) DEFAULT '1',
  `approve_after_editing` tinyint(1) DEFAULT '0',
  `vendor_verification_system` tinyint(1) DEFAULT '1',
  `email_options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `mail_options_account` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `show_vendor_contact_information` tinyint(1) DEFAULT '1',
  `show_vendor_contact_info_guests` tinyint(1) DEFAULT '1',
  `guest_checkout` tinyint(1) DEFAULT '0',
  `maintenance_mode_title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `maintenance_mode_description` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `maintenance_mode_status` tinyint(1) DEFAULT '0',
  `google_adsense_code` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sort_categories` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'category_order',
  `sort_parent_categories_by_order` tinyint(1) DEFAULT '1',
  `pwa_status` tinyint(1) DEFAULT '0',
  `pwa_logo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `location_search_header` tinyint(1) DEFAULT '1',
  `vendor_bulk_product_upload` tinyint(1) DEFAULT '1',
  `vendors_change_shop_name` tinyint(1) DEFAULT '1',
  `show_sold_products` tinyint(1) DEFAULT '1',
  `show_customer_email_seller` tinyint(1) DEFAULT '1',
  `show_customer_phone_seller` tinyint(1) DEFAULT '1',
  `request_documents_vendors` tinyint(1) DEFAULT '0',
  `explanation_documents_vendors` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `allow_free_plan_multiple_times` tinyint(1) DEFAULT '0',
  `single_country_mode` tinyint(1) DEFAULT '0',
  `single_country_id` int DEFAULT NULL,
  `refund_system` tinyint(1) DEFAULT '1',
  `auto_approve_orders` tinyint(1) DEFAULT '0',
  `auto_approve_orders_days` smallint DEFAULT '10',
  `logo_size` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '160x60',
  `profile_number_of_sales` tinyint(1) DEFAULT '1',
  `ai_writer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `product_img_display_mode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'cover',
  `last_cron_update` timestamp NULL DEFAULT NULL,
  `version` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '2.6'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `general_settings`
--

INSERT INTO `general_settings` (`id`, `physical_products_system`, `digital_products_system`, `marketplace_system`, `classified_ads_system`, `bidding_system`, `selling_license_keys_system`, `multi_vendor_system`, `membership_plans_system`, `site_lang`, `timezone`, `application_name`, `selected_navigation`, `fea_categories_design`, `menu_limit`, `turnstile_status`, `turnstile_site_key`, `turnstile_secret_key`, `custom_header_codes`, `custom_footer_codes`, `email_verification`, `storage_settings`, `affiliate_settings`, `email_settings`, `newsletter_settings`, `watermark_settings`, `facebook_app_id`, `facebook_app_secret`, `google_client_id`, `google_client_secret`, `vk_app_id`, `vk_secure_key`, `google_analytics`, `promoted_products`, `multilingual_system`, `product_comments`, `comment_approval_system`, `reviews`, `blog_comments`, `slider_status`, `slider_type`, `slider_effect`, `featured_categories`, `index_promoted_products`, `index_promoted_products_count`, `index_latest_products`, `index_latest_products_count`, `index_blog_slider`, `index_products_per_row`, `product_link_structure`, `site_color`, `logo`, `logo_email`, `favicon`, `facebook_comment`, `facebook_comment_status`, `cache_system`, `static_cache_system`, `category_cache_system`, `refresh_cache_database_changes`, `cache_refresh_time`, `rss_system`, `approve_before_publishing`, `approve_after_editing`, `vendor_verification_system`, `email_options`, `mail_options_account`, `show_vendor_contact_information`, `show_vendor_contact_info_guests`, `guest_checkout`, `maintenance_mode_title`, `maintenance_mode_description`, `maintenance_mode_status`, `google_adsense_code`, `sort_categories`, `sort_parent_categories_by_order`, `pwa_status`, `pwa_logo`, `location_search_header`, `vendor_bulk_product_upload`, `vendors_change_shop_name`, `show_sold_products`, `show_customer_email_seller`, `show_customer_phone_seller`, `request_documents_vendors`, `explanation_documents_vendors`, `allow_free_plan_multiple_times`, `single_country_mode`, `single_country_id`, `refund_system`, `auto_approve_orders`, `auto_approve_orders_days`, `logo_size`, `profile_number_of_sales`, `ai_writer`, `product_img_display_mode`, `last_cron_update`, `version`) VALUES
(1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 'America/New_York', 'Modesy', 1, 'round_boxes', 8, 0, NULL, NULL, NULL, NULL, 0, NULL, 'a:6:{s:6:\"status\";i:0;s:4:\"type\";s:12:\"seller_based\";s:5:\"image\";N;s:7:\"storage\";N;s:15:\"commission_rate\";i:0;s:13:\"discount_rate\";i:0;}', NULL, 'a:4:{s:6:\"status\";s:1:\"1\";s:15:\"is_popup_active\";i:0;s:5:\"image\";N;s:7:\"storage\";N;}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 1, 1, 1, 'full_width', 'fade', 1, 1, 12, 1, 12, 1, 6, 'slug-id', '#00a99d', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 1800, 1, 1, 0, 1, NULL, NULL, 1, 1, 1, 'Coming Soon', 'Our website is under construction. We\'ll be here soon with our new awesome site.', 0, '', 'category_order', 1, 0, NULL, 1, 1, 1, 1, 1, 1, 0, NULL, 0, 0, NULL, 1, 0, 10, '160x60', 1, NULL, 'cover', NULL, '2.6');

-- --------------------------------------------------------

--
-- Table structure for table `homepage_banners`
--

CREATE TABLE `homepage_banners` (
  `id` int NOT NULL,
  `banner_url` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `banner_image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `banner_order` int NOT NULL DEFAULT '1',
  `banner_width` double DEFAULT NULL,
  `banner_location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'featured_products',
  `lang_id` int DEFAULT '1',
  `storage` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `image_default` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_big` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_small` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_main` tinyint(1) DEFAULT '0',
  `is_option_image` tinyint(1) DEFAULT '0',
  `storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `images_file_manager`
--

CREATE TABLE `images_file_manager` (
  `id` int NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int NOT NULL,
  `order_id` bigint DEFAULT NULL,
  `order_number` bigint DEFAULT NULL,
  `client_username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_phone_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_tax_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_state` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `invoice_items` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `knowledge_base`
--

CREATE TABLE `knowledge_base` (
  `id` int NOT NULL,
  `lang_id` tinyint DEFAULT NULL,
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `slug` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `category_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content_order` smallint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `knowledge_base_categories`
--

CREATE TABLE `knowledge_base_categories` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category_order` smallint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `short_form` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `language_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `text_direction` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `language_order` tinyint NOT NULL DEFAULT '1',
  `text_editor_lang` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'en',
  `flag_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`, `short_form`, `language_code`, `text_direction`, `status`, `language_order`, `text_editor_lang`, `flag_path`) VALUES
(1, 'English', 'en', 'en-US', 'ltr', 1, 1, 'en', 'uploads/blocks/flag_eng.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `language_translations`
--

CREATE TABLE `language_translations` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `translation` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `language_translations`
--

INSERT INTO `language_translations` (`id`, `lang_id`, `label`, `translation`) VALUES
(1, 1, 'abuse_reports', 'Abuse Reports'),
(2, 1, 'abuse_report_exp', 'Briefly describe the issue you\'re facing'),
(3, 1, 'abuse_report_msg', 'Your report has reached us. Thank you!'),
(4, 1, 'accept_cookies', 'Accept Cookies'),
(5, 1, 'accept_quote', 'Accept Quote'),
(6, 1, 'access_key', 'Access Key'),
(7, 1, 'account_deletion_requests', 'Account Deletion Requests'),
(8, 1, 'activate_all', 'Activate All'),
(9, 1, 'activate_selected', 'Activate Selected'),
(10, 1, 'activation_email_sent', 'Activation email has been sent!'),
(11, 1, 'active', 'Active'),
(12, 1, 'active_orders', 'Active Orders'),
(13, 1, 'active_payment_request_error', 'You already have an active payment request! Once this is complete, you can make a new request.'),
(14, 1, 'active_sales', 'Active Sales'),
(15, 1, 'added_to_cart', 'Added to Cart'),
(16, 1, 'additional_information', 'Additional Information'),
(17, 1, 'additional_invoice_information', 'Additional Invoice Information'),
(18, 1, 'additional_invoice_information_exp', 'VAT Number, Company No etc.'),
(19, 1, 'address', 'Address'),
(20, 1, 'address_title', 'Address Title'),
(21, 1, 'address_type', 'Address Type'),
(22, 1, 'add_administrator', 'Add Administrator'),
(23, 1, 'add_banner', 'Add Banner'),
(24, 1, 'add_brand', 'Add Brand'),
(25, 1, 'add_category', 'Add Category'),
(26, 1, 'add_city', 'Add City'),
(27, 1, 'add_comment', 'Add Comment'),
(28, 1, 'add_content', 'Add Content'),
(29, 1, 'add_country', 'Add Country'),
(30, 1, 'add_coupon', 'Add Coupon'),
(31, 1, 'add_currency', 'Add Currency'),
(32, 1, 'add_custom_field', 'Add Custom Field'),
(33, 1, 'add_delivery_time', 'Add Delivery Time'),
(34, 1, 'add_external_download_link', 'Add External Download Link'),
(35, 1, 'add_feature', 'Add Feature'),
(36, 1, 'add_font', 'Add Font'),
(37, 1, 'add_funds', 'Add Funds'),
(38, 1, 'add_funds_pay_debt', 'Add funds to your wallet to pay your debt. The debt will be automatically deducted.'),
(39, 1, 'add_image', 'Add Image'),
(40, 1, 'add_language', 'Add Language'),
(41, 1, 'add_license_keys', 'Add License Keys'),
(42, 1, 'add_license_keys_exp', 'Add all license keys with comma(,) separator. (i.e. License Key, License Key...)'),
(43, 1, 'add_new_address', 'Add New Address'),
(44, 1, 'add_option', 'Add Option'),
(45, 1, 'add_page', 'Add Page'),
(46, 1, 'add_payout', 'Add Payout'),
(47, 1, 'add_post', 'Add Post'),
(48, 1, 'add_product', 'Add Product'),
(49, 1, 'add_products', 'Add Products'),
(50, 1, 'add_product_for_sale', 'Add a Product for Sale'),
(51, 1, 'add_product_for_sale_exp', 'Add a product to sell on the site'),
(52, 1, 'add_product_get_price_requests', 'Add a Product to Receive Quote (Price) Requests'),
(53, 1, 'add_product_get_price_requests_exp', 'Add a product without adding a price to get price requests from customers'),
(54, 1, 'add_product_option', 'Add Product Option'),
(55, 1, 'add_product_sell_license_keys', 'Add a Product to Sell License Keys'),
(56, 1, 'add_product_sell_license_keys_exp', 'Add a product to sell only license keys'),
(57, 1, 'add_product_services_listing', 'Add a Product or Service as an Ordinary Listing'),
(58, 1, 'add_product_services_listing_exp', 'Add a product or service without buy option'),
(59, 1, 'add_question', 'Add Question'),
(60, 1, 'add_rate', 'Add Rate'),
(61, 1, 'add_review', 'Add Review'),
(62, 1, 'add_role', 'Add Role'),
(63, 1, 'add_shipping_method', 'Add Shipping Method'),
(64, 1, 'add_shipping_option', 'Add Shipping Option'),
(65, 1, 'add_shipping_zone', 'Add Shipping Zone'),
(66, 1, 'add_slider_item', 'Add Slider Item'),
(67, 1, 'add_space_between_money_currency', 'Add Space Between Money and Currency'),
(68, 1, 'add_state', 'Add State'),
(69, 1, 'add_tag', 'Add Tag'),
(70, 1, 'add_tax', 'Add Tax'),
(71, 1, 'add_to_affiliate_program', 'Add to Affiliate Program'),
(72, 1, 'add_to_cart', 'Add to Cart'),
(73, 1, 'add_to_featured', 'Add to Featured'),
(74, 1, 'add_to_product_filters', 'Add to Product Filters'),
(75, 1, 'add_to_special_offers', 'Add to Special Offers'),
(76, 1, 'add_to_wishlist', 'Add to wishlist'),
(77, 1, 'add_user', 'Add User'),
(78, 1, 'add_value', 'Add Value'),
(79, 1, 'add_video', 'Add Video'),
(80, 1, 'add_watermark_blog_images', 'Add Watermark to Blog Images'),
(81, 1, 'add_watermark_product_images', 'Add Watermark to Product Images'),
(82, 1, 'add_watermark_thumbnail_images', 'Add Watermark to Thumbnail (Small) Images'),
(83, 1, 'administrators', 'Administrators'),
(84, 1, 'admin_emails_will_send', 'Admin emails will be sent to this address'),
(85, 1, 'admin_panel', 'Admin Panel'),
(86, 1, 'admin_panel_link', 'Admin Panel Link'),
(87, 1, 'adsense_head_exp', 'The codes you add here will be added in the <head></head> tags.'),
(88, 1, 'ad_size', 'Ad Size'),
(89, 1, 'ad_spaces', 'Ad Spaces'),
(90, 1, 'affiliate', 'Affiliate'),
(91, 1, 'affiliate_link', 'Affiliate Link'),
(92, 1, 'affiliate_links', 'Affiliate Links'),
(93, 1, 'affiliate_link_exp', 'To maximize your affiliate earnings, share this link in blog posts, on social media, and in email campaigns. Include it in YouTube video descriptions, on your website, and in online forums.'),
(94, 1, 'affiliate_program', 'Affiliate Program'),
(95, 1, 'affiliate_program_vendor_exp', 'The affiliate program allows you, as a seller, to pay external partners a commission for promoting your products and driving sales through their unique links, helping you reach a wider audience and increase revenue. When you activate this system, there will be an option to create an affiliate link on your product page. All users can participate in this program by creating their own links for your products.'),
(96, 1, 'affiliate_seller_based', 'Seller-based (for products selected by the seller, seller pays the commission)'),
(97, 1, 'affiliate_site_based', 'Site-based (for all products, site pays the commission)'),
(98, 1, 'ai_content_creator', 'AI Content Creator'),
(99, 1, 'ai_writer', 'AI Writer'),
(100, 1, 'all', 'All'),
(101, 1, 'allowed_file_extensions', 'Allowed File Extensions'),
(102, 1, 'allow_all_currencies_classified_ads', 'Allow All Currencies for Ordinary Listing'),
(103, 1, 'allow_duplicate_license_keys', 'Allow Duplicate License Keys'),
(104, 1, 'allow_free_plan_multiple_times', 'Allow Free Plan to be Used Multiple Times'),
(105, 1, 'allow_vendors_change_shop_name', 'Allow Vendors to Change Their Shop Name'),
(106, 1, 'all_active_currencies', 'All active currencies'),
(107, 1, 'all_categories', 'All Categories'),
(108, 1, 'all_countries', 'All Countries'),
(109, 1, 'all_help_topics', 'All Help Topics'),
(110, 1, 'all_locations', 'All Locations'),
(111, 1, 'all_permissions', 'All Permissions'),
(112, 1, 'all_sellers', 'All Sellers'),
(113, 1, 'all_states', 'All States'),
(114, 1, 'alphabetically', 'Alphabetically'),
(115, 1, 'already_have_active_request', 'You already have an active request for this product!'),
(116, 1, 'animations', 'Animations'),
(117, 1, 'answer', 'Answer'),
(118, 1, 'api_key', 'Api Key'),
(119, 1, 'appear_on_homepage', 'Appear on the Homepage'),
(120, 1, 'application_key', 'Application Key'),
(121, 1, 'apply', 'Apply'),
(122, 1, 'apply_for_product_sales', 'Apply for Product Sales'),
(123, 1, 'apply_for_service_payments', 'Apply for Service Payments'),
(124, 1, 'approve', 'Approve'),
(125, 1, 'approved', 'Approved'),
(126, 1, 'approved_comments', 'Approved Comments'),
(127, 1, 'approve_refund', 'Approve Refund'),
(128, 1, 'app_id', 'App ID'),
(129, 1, 'app_name', 'Application Name'),
(130, 1, 'app_secret', 'App Secret'),
(131, 1, 'april', 'April'),
(132, 1, 'ask_question', 'Ask Question'),
(133, 1, 'assign_membership_plan', 'Assign Membership Plan'),
(134, 1, 'attachments', 'Attachments'),
(135, 1, 'audio', 'Audio'),
(136, 1, 'audio_preview', 'Audio Preview'),
(137, 1, 'audio_preview_exp', 'MP3 or WAV preview audio'),
(138, 1, 'august', 'August'),
(139, 1, 'automatically_calculated', 'Automatically Calculated'),
(140, 1, 'automatically_update_exchange_rates', 'Automatically Update Exchange Rates'),
(141, 1, 'auto_approve_orders', 'Auto-Approve Unapproved Orders (after x days)'),
(142, 1, 'awaiting_payment', 'Awaiting Payment'),
(143, 1, 'awaiting_sellers_bid', 'Awaiting Seller\'s Bid'),
(144, 1, 'back', 'Back'),
(145, 1, 'balance', 'Balance'),
(146, 1, 'balance_exp', 'Approved earnings'),
(147, 1, 'bank_accounts', 'Bank Accounts'),
(148, 1, 'bank_accounts_exp', 'You can make your payment to one of these bank accounts.'),
(149, 1, 'bank_account_holder_name', 'Bank Account Holder\'s Name'),
(150, 1, 'bank_branch_city', 'Bank Branch City'),
(151, 1, 'bank_branch_country', 'Bank Branch Country'),
(152, 1, 'bank_name', 'Bank Name'),
(153, 1, 'bank_transfer', 'Bank Transfer'),
(154, 1, 'bank_transfer_accepted', 'Bank transfer accepted'),
(155, 1, 'bank_transfer_declined', 'Bank transfer declined'),
(156, 1, 'bank_transfer_exp', 'Make your payment directly into our bank account.'),
(157, 1, 'bank_transfer_reports', 'Bank Transfer Reports'),
(158, 1, 'banned', 'Banned'),
(159, 1, 'banner', 'Banner'),
(160, 1, 'banner_desktop', 'Desktop Banner'),
(161, 1, 'banner_desktop_exp', 'This ad will be displayed on screens larger than 992px'),
(162, 1, 'banner_location_exp', 'The banner will be added under the selected section'),
(163, 1, 'banner_mobile', 'Mobile Banner'),
(164, 1, 'banner_mobile_exp', 'This ad will be displayed on screens smaller than 992px'),
(165, 1, 'banner_width', 'Banner Width'),
(166, 1, 'ban_user', 'Ban User'),
(167, 1, 'base_currency', 'Base Currency'),
(168, 1, 'bidding_system', 'Bidding System'),
(169, 1, 'bidding_system_emails', 'Bidding system emails'),
(170, 1, 'bidding_system_request_quote', 'Bidding System (Request Quote)'),
(171, 1, 'billing_address', 'Billing Address'),
(172, 1, 'bitcoin', 'Bitcoin (BTC)'),
(173, 1, 'blog', 'Blog'),
(174, 1, 'blog_ad_space_1', 'Blog Ad Space 1'),
(175, 1, 'blog_ad_space_2', 'Blog Ad Space 2'),
(176, 1, 'blog_comments', 'Blog Comments'),
(177, 1, 'blog_posts', 'Blog Posts'),
(178, 1, 'blog_slider', 'Blog Slider'),
(179, 1, 'bottom', 'Bottom'),
(180, 1, 'boxed', 'Boxed'),
(181, 1, 'brand', 'Brand'),
(182, 1, 'brands', 'Brands'),
(183, 1, 'browse_files', 'Browse Files'),
(184, 1, 'btc_address', 'BTC Address'),
(185, 1, 'bucket_name', 'Bucket Name'),
(186, 1, 'bulk_category_upload', 'Bulk Category Upload'),
(187, 1, 'bulk_custom_field_upload', 'Bulk Custom Field Upload'),
(188, 1, 'bulk_product_upload', 'Bulk Product Upload'),
(189, 1, 'bulk_product_upload_exp', 'You can add your products with a CSV file from this section'),
(190, 1, 'bulk_upload_documentation', 'Bulk Upload Documentation'),
(191, 1, 'button', 'Button'),
(192, 1, 'button_color', 'Button Color'),
(193, 1, 'button_text', 'Button Text'),
(194, 1, 'button_text_color', 'Button Text Color'),
(195, 1, 'buyer', 'Buyer'),
(196, 1, 'buyer_discount_rate', 'Buyer Discount Rate'),
(197, 1, 'buy_button_link', 'Buy button link'),
(198, 1, 'buy_now', 'Buy Now'),
(199, 1, 'by_category_order', 'by Category Order'),
(200, 1, 'by_date', 'by Date'),
(201, 1, 'cache_refresh_time', 'Cache Refresh Time (Minute)'),
(202, 1, 'cache_refresh_time_exp', 'After this time, your cache files will be refreshed.'),
(203, 1, 'cache_system', 'Cache System'),
(204, 1, 'calculated_weight', 'Calculated Weight'),
(205, 1, 'cancel', 'Cancel'),
(206, 1, 'cancelled', 'Cancelled'),
(207, 1, 'cancelled_sales', 'Cancelled Sales'),
(208, 1, 'cancel_order', 'Cancel Order'),
(209, 1, 'card_number', 'Card Number'),
(210, 1, 'cart', 'Cart'),
(211, 1, 'cart_location_selection', 'Cart Location Selection'),
(212, 1, 'cart_location_selection_exp', 'Require customers to select their location in the cart if it is not already known'),
(213, 1, 'cart_payment', 'Cart Payment'),
(214, 1, 'cash_on_delivery', 'Cash on Delivery'),
(215, 1, 'cash_on_delivery_exp', 'Pay with cash upon delivery.'),
(216, 1, 'cash_on_delivery_vendor_exp', 'Sell your products with pay on delivery option'),
(217, 1, 'cash_on_delivery_warning', 'You have selected \'Cash on Delivery\' as your payment method. You must pay the total amount when you receive your package. If you accept this payment method, please click the button below to complete your order.'),
(218, 1, 'categories', 'Categories'),
(219, 1, 'category', 'Category'),
(220, 1, 'category_cache', 'Category Cache'),
(221, 1, 'category_id_finder', 'Category Id Finder'),
(222, 1, 'category_id_finder_exp', 'You can use this section to find out the Id of a category'),
(223, 1, 'category_level', 'Category Level'),
(224, 1, 'category_name', 'Category Name'),
(225, 1, 'cell_phone', 'Cell Phone'),
(226, 1, 'center', 'Center'),
(227, 1, 'change_password', 'Change Password'),
(228, 1, 'change_user_role', 'Change User Role'),
(229, 1, 'chat_messages', 'Chat Messages'),
(230, 1, 'checkbox', 'Checkbox'),
(231, 1, 'checking_out_as_guest', 'You are checking out as a guest'),
(232, 1, 'checkout', 'Checkout'),
(233, 1, 'choose_plan', 'Choose Plan'),
(234, 1, 'cities', 'Cities'),
(235, 1, 'city', 'City'),
(236, 1, 'classified_ads', 'Classified Ads'),
(237, 1, 'classified_ads_adding_product_as_listing', 'Classified Ads (Adding a Product or Service as an Ordinary Listing)'),
(238, 1, 'clear', 'Clear'),
(239, 1, 'client_id', 'Client ID'),
(240, 1, 'client_information', 'Client Information'),
(241, 1, 'client_secret', 'Client Secret'),
(242, 1, 'close', 'Close'),
(243, 1, 'closed', 'Closed'),
(244, 1, 'close_seller_shop', 'Close Seller\'s Shop'),
(245, 1, 'close_ticket', 'Close Ticket'),
(246, 1, 'close_user_shop', 'Close User Shop'),
(247, 1, 'cloudflare_turnstile', 'Cloudflare Turnstile (CAPTCHA)\r\n'),
(248, 1, 'cm', 'cm'),
(249, 1, 'cod_cancel_exp', 'You can cancel your order within 24 hours after the order date.'),
(250, 1, 'cod_option_disabled', 'Cash on Delivery payment option has been disabled until your commission debt is paid.'),
(251, 1, 'color', 'Color'),
(252, 1, 'comment', 'Comment'),
(253, 1, 'comments', 'Comments'),
(254, 1, 'comment_approval_system', 'Comment Approval System'),
(255, 1, 'commission', 'Commission'),
(256, 1, 'commissions_discounts', 'Commissions & Discounts'),
(257, 1, 'commission_debt', 'Commission Debt'),
(258, 1, 'commission_debt_limit', 'Commission Debt Limit'),
(259, 1, 'commission_debt_limit_exp', 'Cash on Delivery commissions will be automatically deducted from your wallet balance. If your wallet has insufficient funds, the commission will be added as a debt. When these debts exceed the specified debt limit, this payment option will be automatically disabled for your store.'),
(260, 1, 'commission_rate', 'Commission Rate'),
(261, 1, 'completed', 'Completed'),
(262, 1, 'completed_orders', 'Completed Orders'),
(263, 1, 'completed_sales', 'Completed Sales'),
(264, 1, 'complete_payment', 'Complete Payment'),
(265, 1, 'confirm', 'Confirm'),
(266, 1, 'confirmed', 'Confirmed'),
(267, 1, 'confirm_action', 'Are you sure you want to perform this action?'),
(268, 1, 'confirm_and_pay', 'Confirm and Pay'),
(269, 1, 'confirm_approve_order', 'Are you sure you want to confirm this order?'),
(270, 1, 'confirm_close_user_shop', 'Are you sure you want to close this shop?'),
(271, 1, 'confirm_comment', 'Are you sure you want to delete this comment?'),
(272, 1, 'confirm_comments', 'Are you sure you want to delete selected comments?'),
(273, 1, 'confirm_delete', 'Are you sure you want to delete this item?'),
(274, 1, 'confirm_duplicate_product', 'Do you want to create a duplicate of this product? It will be saved as a draft.'),
(275, 1, 'confirm_message', 'Are you sure you want to delete this conversation?'),
(276, 1, 'confirm_messages', 'Are you sure you want to delete selected conversations?'),
(277, 1, 'confirm_order_received', 'Confirm Order Received'),
(278, 1, 'confirm_order_received_exp', 'Confirm if you have received your order.'),
(279, 1, 'confirm_order_received_warning', 'When you receive your order, please check the products you have purchased. If there is not any problem, click \'Confirm Order Received\' button. After confirming your order, the money will be transferred to the seller.'),
(280, 1, 'confirm_payment', 'Confirm Payment'),
(281, 1, 'confirm_product', 'Are you sure you want to delete this product?'),
(282, 1, 'confirm_products', 'Are you sure you want to delete selected products?'),
(283, 1, 'confirm_product_audio', 'Are you sure you want to delete this audio?'),
(284, 1, 'confirm_product_permanent', 'Are you sure you want to permanently delete this product?'),
(285, 1, 'confirm_product_video', 'Are you sure you want to delete this video?'),
(286, 1, 'confirm_quote_request', 'Are you sure you want to delete this quote request?'),
(287, 1, 'confirm_review', 'Are you sure you want to delete this review?'),
(288, 1, 'confirm_reviews', 'Are you sure you want to delete selected reviews?'),
(289, 1, 'confirm_slider_item', 'Are you sure you want to delete this slider item?'),
(290, 1, 'confirm_user', 'Are you sure you want to delete this user?'),
(291, 1, 'confirm_user_email', 'Confirm Email'),
(292, 1, 'confirm_your_account', 'Confirm Your Account'),
(293, 1, 'connect_with_facebook', 'Connect with Facebook'),
(294, 1, 'connect_with_google', 'Connect with Google'),
(295, 1, 'connect_with_vk', 'Connect with VKontakte'),
(296, 1, 'contact', 'Contact'),
(297, 1, 'contact_message', 'Contact Message'),
(298, 1, 'contact_messages', 'Contact Messages'),
(299, 1, 'contact_seller', 'Contact Seller'),
(300, 1, 'contact_settings', 'Contact Settings'),
(301, 1, 'contact_support', 'Contact Support'),
(302, 1, 'contact_support_exp', 'If you didn\'t find what you were looking for, you can submit a support request here.'),
(303, 1, 'contact_text', 'Contact Text'),
(304, 1, 'content', 'Content'),
(305, 1, 'contents', 'Contents'),
(306, 1, 'content_type', 'Content Type'),
(307, 1, 'continent', 'Continent'),
(308, 1, 'continue_to_checkout', 'Continue to Checkout'),
(309, 1, 'continue_to_payment', 'Continue to Payment'),
(310, 1, 'continue_to_payment_method', 'Continue to Payment Method'),
(311, 1, 'cookies_warning', 'Cookies Warning'),
(312, 1, 'copied', 'Copied'),
(313, 1, 'copy', 'Copy'),
(314, 1, 'copyright', 'Copyright'),
(315, 1, 'copy_code', 'Copy Code'),
(316, 1, 'copy_link', 'Copy Link'),
(317, 1, 'cost', 'Cost'),
(318, 1, 'countries', 'Countries'),
(319, 1, 'country', 'Country'),
(320, 1, 'coupon', 'Coupon'),
(321, 1, 'coupons', 'Coupons'),
(322, 1, 'coupon_code', 'Coupon Code'),
(323, 1, 'coupon_minimum_cart_total_exp', 'Minimum cart total needed to use the coupon'),
(324, 1, 'coupon_usage_type', 'Coupon Usage Type'),
(325, 1, 'coupon_usage_type_1', 'Each user can use it for only one order'),
(326, 1, 'coupon_usage_type_2', 'Each user can use it for multiple orders (Guests can use)'),
(327, 1, 'coupon_valid_till', 'Valid till: {field}'),
(328, 1, 'cover_image_type', 'Cover Image Type'),
(329, 1, 'cpf', 'CPF'),
(330, 1, 'create_ad_exp', 'If you don\'t have an ad code, you can create an ad code by selecting an image and adding an URL'),
(331, 1, 'create_affiliate_link', 'Create affiliate link'),
(332, 1, 'create_extra_options', 'Create Extra Options'),
(333, 1, 'create_new_plan', 'Create a New Plan'),
(334, 1, 'create_variants', 'Create Variants'),
(335, 1, 'credit_card', 'Credit Card'),
(336, 1, 'csv_file', 'CSV File'),
(337, 1, 'currencies', 'Currencies'),
(338, 1, 'currency', 'Currency'),
(339, 1, 'currency_code', 'Currency Code'),
(340, 1, 'currency_converter', 'Currency Converter'),
(341, 1, 'currency_converter_api', 'Currency Converter API'),
(342, 1, 'currency_format', 'Currency Format'),
(343, 1, 'currency_name', 'Currency Name'),
(344, 1, 'currency_not_supported', 'The selected payment method cannot be used with the chosen currency. To complete your purchase, please select a supported currency or choose another payment method.'),
(345, 1, 'currency_settings', 'Currency Settings'),
(346, 1, 'currency_symbol', 'Currency Symbol'),
(347, 1, 'currency_symbol_format', 'Currency Symbol Format'),
(348, 1, 'current_plan', 'Current Plan'),
(349, 1, 'custom', 'Custom'),
(350, 1, 'custom_field', 'Custom Field'),
(351, 1, 'custom_fields', 'Custom Fields'),
(352, 1, 'custom_field_options', 'Custom Field Options'),
(353, 1, 'custom_footer_codes', 'Custom Footer Codes'),
(354, 1, 'custom_footer_codes_exp', 'These codes will be added to the footer of the site'),
(355, 1, 'custom_header_codes', 'Custom Header Codes'),
(356, 1, 'custom_header_codes_exp', 'These codes will be added to the header of the site'),
(357, 1, 'cvv', 'CVV'),
(358, 1, 'cvv_exp', 'Three-digits code on the back of your card'),
(359, 1, 'daily', 'Daily'),
(360, 1, 'daily_plan', 'Daily Plan'),
(361, 1, 'dashboard', 'Dashboard'),
(362, 1, 'dashboard_font', 'Dashboard Font'),
(363, 1, 'data_type', 'Data Type'),
(364, 1, 'date', 'Date'),
(365, 1, 'date_of_birth', 'Date of Birth'),
(366, 1, 'day', 'Day'),
(367, 1, 'days', 'Days'),
(368, 1, 'days_ago', 'days ago'),
(369, 1, 'days_left', 'days left'),
(370, 1, 'day_ago', 'day ago'),
(371, 1, 'deactivate_delected', 'Deactivate Selected'),
(372, 1, 'december', 'December'),
(373, 1, 'decimal_seperator', 'Decimal Seperator'),
(374, 1, 'decline', 'Decline'),
(375, 1, 'declined', 'Declined'),
(376, 1, 'default', 'Default'),
(377, 1, 'default_currency', 'Default Currency'),
(378, 1, 'default_language', 'Default Language'),
(379, 1, 'default_variant', 'Default Variant'),
(380, 1, 'delete', 'Delete'),
(381, 1, 'deleted', 'Deleted'),
(382, 1, 'deleted_products', 'Deleted Products'),
(383, 1, 'delete_account', 'Delete Account'),
(384, 1, 'delete_account_exp', 'Deleting your account is permanent and cannot be reversed. All data, including preferences and subscriptions, will be lost. The process requires admin approval, which may take some time. Please enter your password and confirm to proceed.'),
(385, 1, 'delete_account_submit_exp', 'Your account deletion request has been submitted and is awaiting admin approval. If you wish to cancel this request, please contact site administration through the Help Center.'),
(386, 1, 'delete_conversation', 'Delete Conversation'),
(387, 1, 'delete_from_affiliate_program', 'Delete from Affiliate Program'),
(388, 1, 'delete_permanently', 'Delete Permanently'),
(389, 1, 'delete_quote', 'Delete Quote'),
(390, 1, 'delivery_time', 'Delivery Time'),
(391, 1, 'demo_url', 'Demo URL'),
(392, 1, 'demo_url_exp', 'Add a preview URL (i.e. https://demo.com)'),
(393, 1, 'deposits', 'Deposits'),
(394, 1, 'deposit_amount', 'Deposit Amount'),
(395, 1, 'description', 'Description'),
(396, 1, 'details', 'Details'),
(397, 1, 'digital', 'Digital'),
(398, 1, 'digital_exp', 'A digital file that buyers will download'),
(399, 1, 'digital_file', 'Digital File'),
(400, 1, 'digital_files', 'Digital Files'),
(401, 1, 'digital_file_required', 'Digital file is required!'),
(402, 1, 'digital_products', 'Digital Products'),
(403, 1, 'digital_sales', 'Digital Sales'),
(404, 1, 'dimensions', 'Dimensions'),
(405, 1, 'disable', 'Disable'),
(406, 1, 'discord_url', 'Discord Url'),
(407, 1, 'discount', 'Discount'),
(408, 1, 'discounted_price', 'Discounted Price'),
(409, 1, 'discount_coupon', 'Discount Coupon'),
(410, 1, 'discount_rate', 'Discount Rate'),
(411, 1, 'dlocalgo', 'dLocal Go'),
(412, 1, 'documentation', 'Documentation'),
(413, 1, 'domain', 'Domain'),
(414, 1, 'dont_have_account', 'Don\'t have an account?'),
(415, 1, 'dont_want_receive_emails', 'Don\'t want receive these emails?'),
(416, 1, 'download', 'Download'),
(417, 1, 'downloads', 'Downloads'),
(418, 1, 'download_csv_example', 'Download CSV Example'),
(419, 1, 'download_csv_template', 'Download CSV Template'),
(420, 1, 'download_database_backup', 'Download Database Backup'),
(421, 1, 'do_not_have_membership_plan', 'You do not have a membership plan. Click the button below to buy a membership plan.'),
(422, 1, 'draft', 'Draft'),
(423, 1, 'drafts', 'Drafts'),
(424, 1, 'draft_added', 'Draft added successfully!'),
(425, 1, 'drag_drop_file_here', 'Drag and drop file here or'),
(426, 1, 'drag_drop_images_here', 'Drag and drop images here or'),
(427, 1, 'dropdown', 'Dropdown'),
(428, 1, 'duplicate', 'Duplicate'),
(429, 1, 'duration', 'Duration'),
(430, 1, 'earned_amount', 'Earned Amount'),
(431, 1, 'earnings', 'Earnings'),
(432, 1, 'edit', 'Edit'),
(433, 1, 'edited_products', 'Edited Products'),
(434, 1, 'edit_address', 'Edit Address'),
(435, 1, 'edit_banner', 'Edit Banner'),
(436, 1, 'edit_brand', 'Edit Brand'),
(437, 1, 'edit_content', 'Edit Content'),
(438, 1, 'edit_coupon', 'Edit Coupon'),
(439, 1, 'edit_delivery_time', 'Edit Delivery Time'),
(440, 1, 'edit_details', 'Edit Details'),
(441, 1, 'edit_option', 'Edit Option'),
(442, 1, 'edit_options', 'Edit Options'),
(443, 1, 'edit_order', 'Edit Order'),
(444, 1, 'edit_plan', 'Edit Plan'),
(445, 1, 'edit_product', 'Edit Product'),
(446, 1, 'edit_products', 'Edit Products'),
(447, 1, 'edit_role', 'Edit Role'),
(448, 1, 'edit_shipping_zone', 'Edit Shipping Zone'),
(449, 1, 'edit_tax', 'Edit Tax'),
(450, 1, 'edit_translations', 'Edit Translations'),
(451, 1, 'edit_user', 'Edit User'),
(452, 1, 'effect', 'Effect'),
(453, 1, 'email', 'Email'),
(454, 1, 'email_address', 'Email Address'),
(455, 1, 'email_options', 'Email Options'),
(456, 1, 'email_option_contact_messages', 'Send contact messages to email address'),
(457, 1, 'email_option_product_added', 'Send email when a new product is added'),
(458, 1, 'email_option_send_email_new_message', 'Send me an email when someone send me a message'),
(459, 1, 'email_option_send_email_order_shipped', 'Send email to buyer when order shipped'),
(460, 1, 'email_option_send_order_to_buyer', 'Send email to buyer after purchase (Send order summary)'),
(461, 1, 'email_reset_password', 'Please click on the button below to reset your password.'),
(462, 1, 'email_settings', 'Email Settings'),
(463, 1, 'email_status', 'Email Status'),
(464, 1, 'email_template', 'Email Template'),
(465, 1, 'email_text_new_order', 'Your order has been received and is now processed. Your order details are shown below.'),
(466, 1, 'email_text_new_product', 'A new product has been added'),
(467, 1, 'email_text_see_product', 'Click the button below to see the product.'),
(468, 1, 'email_text_thank_for_order', 'Thank you for your order!'),
(469, 1, 'email_verification', 'Email Verification'),
(470, 1, 'enable', 'Enable'),
(471, 1, 'enable_dont_hide_products', 'Enable, Do Not Hide Products'),
(472, 1, 'enable_for_all_products', 'Enable For All Products'),
(473, 1, 'enable_hide_products', 'Enable, Hide Products Until Approved'),
(474, 1, 'enable_only_for_selected_products', 'Enable Only for Selected Products (products can be selected from the products page)'),
(475, 1, 'encryption', 'Encryption'),
(476, 1, 'end', 'End'),
(477, 1, 'endpoint_url', 'Endpoint URL'),
(478, 1, 'enter_amount', 'Enter Amount'),
(479, 1, 'enter_email', 'Enter your email'),
(480, 1, 'enter_topic', 'Enter topic'),
(481, 1, 'enter_two_characters', 'Enter at least 2 characters'),
(482, 1, 'enter_your_password', 'Enter your password'),
(483, 1, 'error_image_limit', 'Image upload limit exceeded!'),
(484, 1, 'error_product_image_delete', 'Before deleting the product image, you need to upload another image for the product!'),
(485, 1, 'error_product_image_required', 'Product image is required! Please upload an image for your product.'),
(486, 1, 'estimated_delivery', 'Estimated Delivery'),
(487, 1, 'example', 'Example'),
(488, 1, 'excel', 'Excel'),
(489, 1, 'exchange_rate', 'Exchange Rate'),
(490, 1, 'expense', 'Expense'),
(491, 1, 'expenses', 'Expenses'),
(492, 1, 'expense_amount', 'Expense Amount'),
(493, 1, 'expiration_date', 'Expiration Date (MM / YY)'),
(494, 1, 'expired', 'Expired'),
(495, 1, 'expired_products', 'Expired Products'),
(496, 1, 'expiry_date', 'Expiry Date'),
(497, 1, 'explanation', 'Explanation'),
(498, 1, 'export', 'Export'),
(499, 1, 'exp_special_characters', 'Do not use special characters'),
(500, 1, 'external_download_link', 'External Download Link'),
(501, 1, 'external_link', 'External Link'),
(502, 1, 'external_link_exp', 'You can add an external product link. (i.e. https://domain.com/product)'),
(503, 1, 'facebook_comments', 'Facebook Comments'),
(504, 1, 'facebook_comments_code', 'Facebook Comments Plugin Code'),
(505, 1, 'facebook_login', 'Facebook Login'),
(506, 1, 'facebook_url', 'Facebook URL'),
(507, 1, 'fade', 'Fade'),
(508, 1, 'favicon', 'Favicon'),
(509, 1, 'feature', 'Feature'),
(510, 1, 'featured', 'Featured'),
(511, 1, 'featured_badge', 'Featured Badge'),
(512, 1, 'featured_categories', 'Featured Categories'),
(513, 1, 'featured_categories_exp', 'Select the categories you want to show under the slider'),
(514, 1, 'featured_products', 'Featured Products'),
(515, 1, 'featured_products_payment_currency', 'Featured Products Payment Currency'),
(516, 1, 'featured_products_system', 'Featured Products System'),
(517, 1, 'featured_product_fees', 'Featured (Promoted) Product Fees'),
(518, 1, 'features', 'Features'),
(519, 1, 'february', 'February'),
(520, 1, 'field', 'Field'),
(521, 1, 'field_name', 'Field Name'),
(522, 1, 'files_included', 'Files Included'),
(523, 1, 'files_included_ext', 'Enter the extensions of the files that you are going to sell (i.e. JPG, MP4, MP3)'),
(524, 1, 'file_too_large', 'File is too large. Max file size:'),
(525, 1, 'file_upload', 'File Upload'),
(526, 1, 'filter', 'Filter'),
(527, 1, 'filter_by_keyword', 'Filter by keyword'),
(528, 1, 'filter_key', 'Filter Key'),
(529, 1, 'filter_key_exp', 'Don\'t add special characters'),
(530, 1, 'filter_products', 'Filter Products'),
(531, 1, 'filter_products_location', 'Filter products by location'),
(532, 1, 'first_name', 'First Name'),
(533, 1, 'flag', 'Flag'),
(534, 1, 'flat_rate', 'Flat Rate'),
(535, 1, 'flat_rate_exp', 'Standard shipping'),
(536, 1, 'flat_rate_rules', 'Flat Rate Rules'),
(537, 1, 'flutterwave', 'Flutterwave'),
(538, 1, 'folder_name', 'Folder Name'),
(539, 1, 'follow', 'Follow'),
(540, 1, 'followers', 'Followers'),
(541, 1, 'following', 'Following'),
(542, 1, 'follow_us', 'Follow Us'),
(543, 1, 'fonts', 'Fonts'),
(544, 1, 'font_family', 'Font Family'),
(545, 1, 'font_settings', 'Font Settings'),
(546, 1, 'font_size', 'Font Size'),
(547, 1, 'footer', 'Footer'),
(548, 1, 'footer_about_section', 'Footer About Section'),
(549, 1, 'footer_bottom', 'Footer Bottom'),
(550, 1, 'footer_information', 'Information'),
(551, 1, 'footer_quick_links', 'Quick Links'),
(552, 1, 'forgot_password', 'Forgot Password?'),
(553, 1, 'form_validation_is_unique', 'The {field} field must contain a unique value.'),
(554, 1, 'form_validation_matches', 'The {field} field does not match the {param} field.'),
(555, 1, 'form_validation_max_length', 'The {field} field cannot exceed {param} characters in length.'),
(556, 1, 'form_validation_min_length', 'The {field} field must be at least {param} characters in length.'),
(557, 1, 'form_validation_required', 'The {field} field is required.'),
(558, 1, 'free', 'Free'),
(559, 1, 'free_product', 'Free Product'),
(560, 1, 'free_promotion', 'Free Promotion'),
(561, 1, 'free_shipping', 'Free Shipping'),
(562, 1, 'free_shipping_exp', 'Get your order delivered for free'),
(563, 1, 'frequency', 'Frequency'),
(564, 1, 'frequency_exp', 'This value indicates how frequently the content at a particular URL is likely to change '),
(565, 1, 'frequently_asked_questions', 'Frequently Asked Questions'),
(566, 1, 'friday', 'Friday'),
(567, 1, 'from', 'From:'),
(568, 1, 'full_name', 'Full Name'),
(569, 1, 'full_width', 'Full Width'),
(570, 1, 'general', 'General'),
(571, 1, 'general_information', 'General Information'),
(572, 1, 'general_settings', 'General Settings'),
(573, 1, 'generate', 'Generate'),
(574, 1, 'generated_sitemaps', 'Generated Sitemaps'),
(575, 1, 'generated_text', 'Generated Text'),
(576, 1, 'generate_sitemap', 'Generate Sitemap'),
(577, 1, 'generate_text', 'Generate Text'),
(578, 1, 'generating_text', 'Generating text...'),
(579, 1, 'global', 'Global'),
(580, 1, 'global_taxes', 'Global Taxes'),
(581, 1, 'global_taxes_exp', 'Define new taxes by country for all sales on your site'),
(582, 1, 'google_adsense_code', 'Google Adsense Code'),
(583, 1, 'google_analytics', 'Google Analytics Code'),
(584, 1, 'google_login', 'Google Login'),
(585, 1, 'google_url', 'Google+ URL'),
(586, 1, 'goto_home', 'Go to the Homepage'),
(587, 1, 'go_back_to_products', 'Go Back to the Products Page'),
(588, 1, 'go_back_to_shop_settings', 'Go Back to the Shop Settings'),
(589, 1, 'go_to_your_product', 'Go to Your Product'),
(590, 1, 'grid_layout', 'Grid Layout'),
(591, 1, 'guest', 'Guest'),
(592, 1, 'guest_checkout', 'Guest Checkout'),
(593, 1, 'have_account', 'Have an account?'),
(594, 1, 'header', 'Header'),
(595, 1, 'height', 'Height'),
(596, 1, 'help_center', 'Help Center'),
(597, 1, 'help_documents', 'Help Documents'),
(598, 1, 'help_documents_exp', 'You can use these documents to generate your CSV file\r\n'),
(599, 1, 'hi', 'Hi'),
(600, 1, 'hidden', 'Hidden'),
(601, 1, 'hidden_products', 'Hidden Products'),
(602, 1, 'hide', 'Hide'),
(603, 1, 'highest_price', 'Highest Price'),
(604, 1, 'highest_rating', 'Highest Rating'),
(605, 1, 'home', 'Home'),
(606, 1, 'homepage', 'Homepage'),
(607, 1, 'homepage_banners', 'Homepage Banners'),
(608, 1, 'homepage_banners_exp', 'You can manage the product banners on the homepage from this section'),
(609, 1, 'homepage_manager', 'Homepage Manager'),
(610, 1, 'homepage_title', 'Homepage Title'),
(611, 1, 'horizontal_alignment', 'Horizontal Alignment'),
(612, 1, 'hours_ago', 'hours ago'),
(613, 1, 'hour_ago', 'hour ago'),
(614, 1, 'how_can_we_help', 'How can we help?'),
(615, 1, 'how_it_works', 'How It Works'),
(616, 1, 'iban', 'IBAN'),
(617, 1, 'iban_long', 'International Bank Account Number'),
(618, 1, 'id', 'Id'),
(619, 1, 'identity_number', 'Identity Number'),
(620, 1, 'if_review_already_added', 'If you have already added a review, your review will be updated.'),
(621, 1, 'image', 'Image'),
(622, 1, 'images', 'Images'),
(623, 1, 'image_file_format', 'Image File Format'),
(624, 1, 'image_file_format_exp', 'Uploaded images will be converted to the selected format'),
(625, 1, 'img_display_fill_container', 'Fill Container (Crop to Fit)'),
(626, 1, 'img_display_show_full_image', 'Show Full Image (Fit Inside)'),
(627, 1, 'import_language', 'Import Language'),
(628, 1, 'inactivate_all', 'Inactivate All'),
(629, 1, 'inactive', 'Inactive'),
(630, 1, 'index_ad_space_1', 'Index Ad Space 1'),
(631, 1, 'index_ad_space_2', 'Index Ad Space 2'),
(632, 1, 'index_slider', 'Index Slider'),
(633, 1, 'input_explanation', 'Input Explanation'),
(634, 1, 'instagram_url', 'Instagram URL'),
(635, 1, 'instant_download', 'Instant download'),
(636, 1, 'invalid_attempt', 'Invalid Attempt!'),
(637, 1, 'invalid_file_type', 'Invalid file type!'),
(638, 1, 'invalid_withdrawal_amount', 'Invalid withdrawal amount!'),
(639, 1, 'invoice', 'Invoice'),
(640, 1, 'invoices', 'Invoices'),
(641, 1, 'invoice_currency_warning', 'All amounts shown on this invoice are in'),
(642, 1, 'in_stock', 'In Stock'),
(643, 1, 'ip_address', 'Ip Address'),
(644, 1, 'item', 'Item'),
(645, 1, 'iyzico', 'Iyzico'),
(646, 1, 'iyzico_warning', 'This is the \"Checkout Form\" integration, not the \"Marketplace\" integration.'),
(647, 1, 'january', 'January'),
(648, 1, 'joined_affiliate_program', 'You joined the affiliate program'),
(649, 1, 'join_newsletter', 'Join Our Newsletter'),
(650, 1, 'join_program', 'Join Program'),
(651, 1, 'json_language_file', 'JSON Language File'),
(652, 1, 'july', 'July'),
(653, 1, 'june', 'June'),
(654, 1, 'just_now', 'Just Now'),
(655, 1, 'keep_original_file_format', 'Keep Original File Format'),
(656, 1, 'keep_shopping', 'Keep Shopping'),
(657, 1, 'keyword', 'Keyword'),
(658, 1, 'keywords', 'Keywords'),
(659, 1, 'key_id', 'Key ID'),
(660, 1, 'kg', 'kg'),
(661, 1, 'knowledge_base', 'Knowledge Base'),
(662, 1, 'label', 'Label'),
(663, 1, 'language', 'Language'),
(664, 1, 'languages', 'Languages'),
(665, 1, 'language_code', 'Language Code'),
(666, 1, 'language_name', 'Language Name'),
(667, 1, 'language_settings', 'Language Settings'),
(668, 1, 'last_modification', 'Last Modification'),
(669, 1, 'last_modification_exp', 'The time the URL was last modified'),
(670, 1, 'last_name', 'Last Name'),
(671, 1, 'last_seen', 'Last seen: '),
(672, 1, 'last_update', 'Last Update'),
(673, 1, 'latest_blog_posts', 'Latest Blog Posts'),
(674, 1, 'latest_comments', 'Latest Comments'),
(675, 1, 'latest_members', 'Latest Members'),
(676, 1, 'latest_orders', 'Latest Orders'),
(677, 1, 'latest_pending_products', 'Latest Pending Products'),
(678, 1, 'latest_posts', 'Latest Posts'),
(679, 1, 'latest_products', 'Latest Products'),
(680, 1, 'latest_reviews', 'Latest Reviews'),
(681, 1, 'latest_sales', 'Latest Sales'),
(682, 1, 'latest_transactions', 'Latest Transactions'),
(683, 1, 'leave_message', 'Leave Message'),
(684, 1, 'left', 'Left'),
(685, 1, 'left_to_right', 'Left to Right (LTR)'),
(686, 1, 'length', 'Length'),
(687, 1, 'length_of_text', 'Length of Text'),
(688, 1, 'license_certificate', 'License Certificate'),
(689, 1, 'license_key', 'License Key'),
(690, 1, 'license_keys', 'License Keys'),
(691, 1, 'license_keys_system_exp', 'Add all your license keys from here. The system will automatically give a license key to each buyer.'),
(692, 1, 'link', 'Link'),
(693, 1, 'linkedin_url', 'Linkedin URL'),
(694, 1, 'listing_type', 'Listing Type'),
(695, 1, 'live_preview', 'Live Preview'),
(696, 1, 'loading_payment_options', 'Loading payment options...'),
(697, 1, 'load_more', 'Load More'),
(698, 1, 'load_more_comments', 'Load more comments'),
(699, 1, 'load_more_reviews', 'Load more reviews'),
(700, 1, 'local_pickup', 'Local Pickup'),
(701, 1, 'local_pickup_cost', 'Local Pickup Cost'),
(702, 1, 'local_pickup_exp', 'Pick up your order directly from the store'),
(703, 1, 'local_storage', 'Local Storage'),
(704, 1, 'location', 'Location'),
(705, 1, 'login', 'Login'),
(706, 1, 'login_error', 'Wrong email or password!'),
(707, 1, 'login_to_user_account_exp', 'Your current session will be terminated and a new session will be created for the account of the user you selected.'),
(708, 1, 'login_with_email', 'Or login with email'),
(709, 1, 'logo', 'Logo'),
(710, 1, 'logout', 'Logout'),
(711, 1, 'logo_email', 'Logo Email'),
(712, 1, 'logo_size', 'Logo Size'),
(713, 1, 'long', 'Long'),
(714, 1, 'lowest_price', 'Lowest Price'),
(715, 1, 'mail', 'Mail'),
(716, 1, 'mail_host', 'Mail Host'),
(717, 1, 'mail_is_being_sent', 'Mail is being sent. Please do not close this page until the process is finished!'),
(718, 1, 'mail_password', 'Mail Password'),
(719, 1, 'mail_port', 'Mail Port'),
(720, 1, 'mail_protocol', 'Mail Protocol'),
(721, 1, 'mail_service', 'Mail Service'),
(722, 1, 'mail_title', 'Mail Title'),
(723, 1, 'mail_username', 'Mail Username'),
(724, 1, 'main', 'main'),
(725, 1, 'maintenance_mode', 'Maintenance Mode'),
(726, 1, 'main_files', 'Main File(s)'),
(727, 1, 'main_menu', 'Main Menu'),
(728, 1, 'management_tools', 'Management Tools'),
(729, 1, 'manage_images', 'Manage Images'),
(730, 1, 'march', 'March'),
(731, 1, 'marketplace', 'Marketplace'),
(732, 1, 'marketplace_selling_product_on_the_site', 'Marketplace (Selling Products on the Site)'),
(733, 1, 'max', 'Max'),
(734, 1, 'max_file_size', 'Max File Size'),
(735, 1, 'max_weight', 'Max Weight'),
(736, 1, 'may', 'May'),
(737, 1, 'medium', 'Medium'),
(738, 1, 'member', 'Member'),
(739, 1, 'members', 'Members'),
(740, 1, 'membership', 'Membership'),
(741, 1, 'membership_number_of_ads', 'Number of Active Ads'),
(742, 1, 'membership_payments', 'Membership Payments'),
(743, 1, 'membership_plan', 'Membership Plan'),
(744, 1, 'membership_plans', 'Membership Plans'),
(745, 1, 'membership_plan_payment', 'Membership Plan Payment'),
(746, 1, 'member_since', 'Member since'),
(747, 1, 'menu_limit', 'Menu Limit'),
(748, 1, 'message', 'Message'),
(749, 1, 'messages', 'Messages'),
(750, 1, 'meta_description', 'Meta Description'),
(751, 1, 'meta_keywords', 'Meta Keywords'),
(752, 1, 'meta_tag', 'Meta Tag'),
(753, 1, 'meta_title', 'Meta Title'),
(754, 1, 'method_name', 'Method Name'),
(755, 1, 'mgs_reject_open_shop', 'Your request to open a store has been rejected!'),
(756, 1, 'mgs_reject_open_shop_permanently', 'Your request to open a store has been permanently rejected!'),
(757, 1, 'midtrans', 'Midtrans'),
(758, 1, 'min', 'Min'),
(759, 1, 'minimum_deposit_amount', 'Minimum Deposit Amount'),
(760, 1, 'minimum_order_amount', 'Minimum order amount'),
(761, 1, 'minutes_ago', 'minutes ago'),
(762, 1, 'minute_ago', 'minute ago'),
(763, 1, 'min_poyout_amount', 'Minimum payout amount'),
(764, 1, 'min_poyout_amounts', 'Minimum Payout Amounts'),
(765, 1, 'min_weight', 'Min Weight'),
(766, 1, 'mode', 'Mode'),
(767, 1, 'model', 'Model'),
(768, 1, 'monday', 'Monday'),
(769, 1, 'month', 'Month'),
(770, 1, 'monthly', 'Monthly'),
(771, 1, 'monthly_plan', 'Monthly Plan'),
(772, 1, 'monthly_sales', 'Monthly sales'),
(773, 1, 'months_ago', 'months ago'),
(774, 1, 'month_ago', 'month ago'),
(775, 1, 'month_count', 'Number of Months'),
(776, 1, 'more', 'More'),
(777, 1, 'more_from', 'More from'),
(778, 1, 'most_recent', 'Most Recent'),
(779, 1, 'most_viewed_products', 'Most Viewed Products'),
(780, 1, 'msg_accept_terms', 'You have to accept the terms!'),
(781, 1, 'msg_added', 'Item successfully added!'),
(782, 1, 'msg_add_license_keys', 'License keys successfully added!'),
(783, 1, 'msg_add_product_success', 'Your payment has been successfully completed! Once your product is approved, it will be published on our site!'),
(784, 1, 'msg_administrator_added', 'Administrator successfully added!'),
(785, 1, 'msg_bank_transfer_text', 'Once you have placed your order, you can make your payment to one of these bank accounts. Please add your order number to your payment description.'),
(786, 1, 'msg_bank_transfer_text_order_completed', 'You can make your payment to one of these bank accounts. Please add your order number to your payment description.'),
(787, 1, 'msg_bank_transfer_text_transaction_completed', 'You can make your payment to one of these bank accounts. Please add your transaction number to your payment description.'),
(788, 1, 'msg_ban_error', 'Your account has been suspended!'),
(789, 1, 'msg_bot_verification_failed', 'Bot verification failed. Please try again.'),
(790, 1, 'msg_cart_login_error', 'Please log in to complete your purchase!'),
(791, 1, 'msg_cart_select_location', 'Please select your location to proceed with your purchase.'),
(792, 1, 'msg_cart_shipping', 'Please enter your shipping address and choose a shipping method!'),
(793, 1, 'msg_change_password_error', 'There was a problem changing your password!'),
(794, 1, 'msg_change_password_success', 'Your password has been successfully changed!'),
(795, 1, 'msg_change_product_option_type', 'Changing the option type will delete all existing values/variants for this option. Are you sure you want to continue?'),
(796, 1, 'msg_comment_approved', 'Comment successfully approved!'),
(797, 1, 'msg_comment_deleted', 'Comment successfully deleted!'),
(798, 1, 'msg_comment_sent_successfully', 'Your comment has been sent. It will be published after being reviewed by the site management.'),
(799, 1, 'msg_complete_payment', 'Please click the button below to complete the payment.'),
(800, 1, 'msg_confirmation_email', 'Please click on the button below to confirm your account.'),
(801, 1, 'msg_confirmed', 'Your email address has been successfully confirmed!'),
(802, 1, 'msg_confirmed_required', 'In order to login to the site, you must confirm your email address.'),
(803, 1, 'msg_contact_error', 'There was a problem sending your message!'),
(804, 1, 'msg_coupon_auth', 'This coupon is for registered members only!'),
(805, 1, 'msg_coupon_cart_total', 'Your cart total is not enough to use this coupon. Minimum cart total:'),
(806, 1, 'msg_coupon_code_added_before', 'This coupon code has already been added before. Please add another coupon code.'),
(807, 1, 'msg_coupon_limit', 'Coupon usage limit has been reached!'),
(808, 1, 'msg_coupon_used', 'This coupon code has been used before!'),
(809, 1, 'msg_cron_sitemap', 'With this URL you can automatically update your sitemap.'),
(810, 1, 'msg_default_language_delete', 'Default language cannot be deleted!'),
(811, 1, 'msg_default_page_delete', 'Default pages cannot be deleted!'),
(812, 1, 'msg_default_variant', 'The default variant will automatically use the product\'s main price, discounted price, stock quantity and weight.'),
(813, 1, 'msg_deleted', 'Item successfully deleted!'),
(814, 1, 'msg_delete_posts', 'Please delete posts belonging to this category first!'),
(815, 1, 'msg_delete_subcategories', 'Please delete subcategories belonging to this category first!'),
(816, 1, 'msg_digital_product_register_error', 'You must create an account to purchase a digital product.'),
(817, 1, 'msg_dont_have_downloadable_files', 'You don\'t have any downloadable files.'),
(818, 1, 'msg_email_sent', 'Email successfully sent!'),
(819, 1, 'msg_email_unique_error', 'The email has already been taken.'),
(820, 1, 'msg_error', 'An error occurred please try again!'),
(821, 1, 'msg_error_product_type', 'You must enable at least one product type'),
(822, 1, 'msg_error_selected_system', 'You must enable at least one system'),
(823, 1, 'msg_error_sku', 'This SKU is used for your another product!'),
(824, 1, 'msg_expired_plan', 'When your plan expires, if you do not renew your plan within 3 days, your ads will be added to the \"Expired Products\" section and will not be displayed on the site.'),
(825, 1, 'msg_insufficient_balance', 'Insufficient balance!'),
(826, 1, 'msg_invalid_coupon', 'This coupon code is invalid or has expired!'),
(827, 1, 'msg_invalid_email', 'Invalid email address!'),
(828, 1, 'msg_membership_activated', 'Your membership plan has been successfully activated!'),
(829, 1, 'msg_membership_renewed', 'Your membership plan has been successfully renewed!'),
(830, 1, 'msg_message_deleted', 'Message successfully deleted!'),
(831, 1, 'msg_message_sent', 'Your message has been successfully sent!'),
(832, 1, 'msg_message_sent_error', 'You cannot send message to yourself!'),
(833, 1, 'msg_newsletter_error', 'Your email address is already registered!'),
(834, 1, 'msg_newsletter_success', 'Your email address has been successfully added!'),
(835, 1, 'msg_option_exists', 'This option already exists!'),
(836, 1, 'msg_order_completed', 'Your order has been successfully completed!'),
(837, 1, 'msg_payment_completed', 'Payment completed successfully!'),
(838, 1, 'msg_payment_database_error', 'Your payment has been successfully completed, but there was a problem with adding the order to the database! Please contact our site management for this order!'),
(839, 1, 'msg_payment_error', 'An error occurred during the payment!'),
(840, 1, 'msg_payment_success', 'Your payment has been successfully completed!'),
(841, 1, 'msg_payout_bitcoin_address_error', 'You must enter your BTC address to make this payment request'),
(842, 1, 'msg_payout_paypal_error', 'You must enter your PayPal email address to make this payment request'),
(843, 1, 'msg_plan_expired', 'Your membership plan has expired!'),
(844, 1, 'msg_processing_coupon_assignments', 'Processing coupon assignments. Please do not close this window.'),
(845, 1, 'msg_product_already_purchased', 'You have already purchased this product before.'),
(846, 1, 'msg_product_approved', 'Product successfully approved!'),
(847, 1, 'msg_product_slug_used', 'This slug is used by another product!'),
(848, 1, 'msg_promote_bank_transfer_text', 'Once you have placed your order, you can make your payment to one of these bank accounts. Please add your transaction number to your payment description.'),
(849, 1, 'msg_quote_request_error', 'You cannot request a quote for your own item!'),
(850, 1, 'msg_quote_request_sent', 'Your request has been successfully submitted.'),
(851, 1, 'msg_reached_ads_limit', 'You have reached your ad adding limit! If you want to add more ads, you can upgrade your current plan by clicking the button below.'),
(852, 1, 'msg_refund_request_email', 'You have received a refund request. Please click the button below to see the details.'),
(853, 1, 'msg_refund_request_update_email', 'There is an update for your refund request. Please click the button below to see the details.'),
(854, 1, 'msg_register_success', 'Your account has been created successfully!'),
(855, 1, 'msg_request_received', 'Your request has been received!'),
(856, 1, 'msg_request_sent', 'The request has been sent successfully!'),
(857, 1, 'msg_reset_cache', 'All cache files have been deleted!'),
(858, 1, 'msg_reset_password_error', 'We can\'t find a user with that e-mail address!'),
(859, 1, 'msg_reset_password_success', 'We\'ve sent an email for resetting your password to your email address. Please check your email for next steps.'),
(860, 1, 'msg_review_added', 'Your review has been successfully added!'),
(861, 1, 'msg_review_deleted', 'Review successfully deleted!'),
(862, 1, 'msg_send_confirmation_email', 'An activation email has been sent to your email address. Please confirm your account.'),
(863, 1, 'msg_shop_name_unique_error', 'The shop name has already been taken.'),
(864, 1, 'msg_shop_opening_requests', 'Your request to open a store is under evaluation!'),
(865, 1, 'msg_sitemap_updated', 'Sitemap successfully updated!'),
(866, 1, 'msg_slug_unique_error', 'The slug has already been taken.'),
(867, 1, 'msg_start_selling', 'We have received your request. Your store will be open when your request is approved.'),
(868, 1, 'msg_subscriber_deleted', 'Subscriber successfully deleted!'),
(869, 1, 'msg_support_message_received', 'Your Support Message has been Received'),
(870, 1, 'msg_support_message_received_exp', 'Thank you for reaching out to us. We have received your support message and will get back to you shortly.'),
(871, 1, 'msg_support_message_replied', 'Your Support Ticket Has Been Replied'),
(872, 1, 'msg_support_message_replied_exp', 'Please click the button below to view the ticket details.'),
(873, 1, 'msg_support_new_message', 'New Support Message'),
(874, 1, 'msg_tag_exists', 'This tag already exists!'),
(875, 1, 'msg_topic_empty', 'Topic cannot be empty!'),
(876, 1, 'msg_unsubscribe', 'You will no longer receive emails from us!'),
(877, 1, 'msg_updated', 'Changes successfully saved!'),
(878, 1, 'msg_username_unique_error', 'The username has already been taken.'),
(879, 1, 'msg_vendor_membership_plan_expired', 'Your membership plan has expired, so your products will no longer be published on the site. If you would like your products to continue being published on the site, please renew your membership plan.'),
(880, 1, 'msg_verification_required', 'Verification is required.'),
(881, 1, 'msg_wrong_old_password', 'Wrong old password!'),
(882, 1, 'multilingual_system', 'Multilingual System'),
(883, 1, 'multiple_sale', 'Multiple Sale'),
(884, 1, 'multiple_sale_option_1', 'Yes, I want to sell this product to more than one customer'),
(885, 1, 'multiple_sale_option_2', 'No, I want to sell this product to a single customer'),
(886, 1, 'multi_select', 'Multi Select'),
(887, 1, 'multi_vendor_system', 'Multi-Vendor System'),
(888, 1, 'multi_vendor_system_exp', 'If you disable it, only Admin can add product.'),
(889, 1, 'my_account', 'My Account'),
(890, 1, 'my_cart', 'My Cart'),
(891, 1, 'my_coupons', 'My Coupons'),
(892, 1, 'my_reviews', 'My Reviews'),
(893, 1, 'name', 'Name'),
(894, 1, 'name_on_the_card', 'Name on the Card'),
(895, 1, 'navigation', 'Navigation'),
(896, 1, 'navigation_template', 'Navigation Template'),
(897, 1, 'need_more_help', 'Need more help?'),
(898, 1, 'never', 'Never'),
(899, 1, 'new', 'New');
INSERT INTO `language_translations` (`id`, `lang_id`, `label`, `translation`) VALUES
(900, 1, 'newsletter', 'Newsletter'),
(901, 1, 'newsletter_desc', 'Join our subscribers list to get the latest news, updates and special offers directly in your inbox'),
(902, 1, 'newsletter_popup', 'Newsletter Popup'),
(903, 1, 'newsletter_send_many_exp', 'Some servers do not allow mass mailing. Therefore, instead of sending your mails to all subscribers at once, you can send them part by part (Example: 50 subscribers at once). If your mail server stops sending mail, the sending process will also stop.'),
(904, 1, 'new_arrivals', 'New Arrivals'),
(905, 1, 'new_message', 'New'),
(906, 1, 'new_password', 'New Password'),
(907, 1, 'new_payout_request', 'New Payout Request'),
(908, 1, 'new_quote_request', 'New Quote Request'),
(909, 1, 'next', 'Next'),
(910, 1, 'no', 'No'),
(911, 1, 'none', 'None'),
(912, 1, 'not_added_shipping_address', 'You have not added a shipping address yet.'),
(913, 1, 'not_added_vendor_balance', 'Not Added to Vendor Balance'),
(914, 1, 'november', 'November'),
(915, 1, 'no_comments_found', 'No comments found for this product. Be the first to comment!'),
(916, 1, 'no_delivery_is_made_to_address', 'No delivery is made to the address you have chosen.'),
(917, 1, 'no_delivery_this_location', 'No delivery to this location'),
(918, 1, 'no_discount', 'No Discount'),
(919, 1, 'no_members_found', 'No members found!'),
(920, 1, 'no_messages_found', 'No messages found!'),
(921, 1, 'no_products_found', 'No products found!'),
(922, 1, 'no_records_found', 'No records found!'),
(923, 1, 'no_results_found', 'No Results Found!'),
(924, 1, 'no_reviews_found', 'No reviews found!'),
(925, 1, 'no_vat', 'No VAT'),
(926, 1, 'number', 'Number'),
(927, 1, 'number_featured_products', 'Number of Featured Products to Show'),
(928, 1, 'number_input', 'Number Input'),
(929, 1, 'number_latest_products', 'Number of Latest Products to Show'),
(930, 1, 'number_of_ads', 'Number of Ads'),
(931, 1, 'number_of_coupons', 'Number of Coupons'),
(932, 1, 'number_of_coupons_exp', 'How many times a coupon can be used by all customers before being invalid'),
(933, 1, 'number_of_days', 'Number of Days'),
(934, 1, 'number_of_entries', 'Number of Entries'),
(935, 1, 'number_of_links_in_menu', 'The number of links that appear in the menu'),
(936, 1, 'number_of_results', 'Number of Results'),
(937, 1, 'number_of_total_sales', 'Number of total sales'),
(938, 1, 'number_remaining_ads', 'Number of Remaining Ads'),
(939, 1, 'number_short_billion', 'b'),
(940, 1, 'number_short_million', 'm'),
(941, 1, 'number_short_thousand', 'k'),
(942, 1, 'num_articles', '{field} Articles'),
(943, 1, 'october', 'October'),
(944, 1, 'offline', 'Offline'),
(945, 1, 'ok', 'OK'),
(946, 1, 'old_password', 'Old Password'),
(947, 1, 'online', 'Online'),
(948, 1, 'only_affiliate_sellers', 'Only Affiliate Sellers'),
(949, 1, 'open', 'Open'),
(950, 1, 'open_user_shop', 'Open User Shop'),
(951, 1, 'option', 'Option'),
(952, 1, 'optional', 'Optional'),
(953, 1, 'options', 'Options'),
(954, 1, 'option_name', 'Option Name'),
(955, 1, 'option_name_placeholder', 'e.g. Color, Size'),
(956, 1, 'option_type', 'Option Type'),
(957, 1, 'order', 'Order'),
(958, 1, 'orders', 'Orders'),
(959, 1, 'order_details', 'Order Details'),
(960, 1, 'order_has_been_shipped', 'The order has been shipped!'),
(961, 1, 'order_id', 'Order Id'),
(962, 1, 'order_information', 'Order Information'),
(963, 1, 'order_not_yet_shipped', 'The order has not yet been shipped.'),
(964, 1, 'order_number', 'Order Number'),
(965, 1, 'order_processing', 'Processing'),
(966, 1, 'order_summary', 'Order Summary'),
(967, 1, 'ordinary_listing', 'Ordinary Listing'),
(968, 1, 'out_of_stock', 'Out of Stock'),
(969, 1, 'pages', 'Pages'),
(970, 1, 'page_not_found', 'Page not found'),
(971, 1, 'page_not_found_sub', 'The page you are looking for doesn\'t exist.'),
(972, 1, 'page_type', 'Page Type'),
(973, 1, 'page_views', 'Page Views'),
(974, 1, 'pagination_product', 'Pagination (Number of products on each page)'),
(975, 1, 'paid', 'Paid'),
(976, 1, 'panel', 'Panel'),
(977, 1, 'parent', 'Parent'),
(978, 1, 'parent_category', 'Parent Category'),
(979, 1, 'password', 'Password'),
(980, 1, 'password_confirm', 'Confirm Password'),
(981, 1, 'password_reset', 'Password Reset'),
(982, 1, 'paste_ad_code', 'Paste Ad Code'),
(983, 1, 'paste_ad_url', 'Paste Ad URL'),
(984, 1, 'pay', 'Pay'),
(985, 1, 'payer_email', 'Payer Email'),
(986, 1, 'paying_wallet_balance', 'Paying with Wallet Balance'),
(987, 1, 'payment', 'Payment'),
(988, 1, 'payments', 'Payments'),
(989, 1, 'payment_amount', 'Payment Amount'),
(990, 1, 'payment_cancelled', 'Payment has been cancelled!'),
(991, 1, 'payment_details', 'Payment Details'),
(992, 1, 'payment_id', 'Payment Id'),
(993, 1, 'payment_method', 'Payment Method'),
(994, 1, 'payment_note', 'Payment Note'),
(995, 1, 'payment_option_load_error', 'The selected payment method is currently unavailable. Please choose a different method or try again later.'),
(996, 1, 'payment_received', 'Payment Received'),
(997, 1, 'payment_settings', 'Payment Settings'),
(998, 1, 'payment_status', 'Payment Status'),
(999, 1, 'payouts', 'Payouts'),
(1000, 1, 'payout_requests', 'Payout Requests'),
(1001, 1, 'payout_settings', 'Payout Settings'),
(1002, 1, 'paypal', 'PayPal'),
(1003, 1, 'paypal_email_address', 'PayPal Email Address'),
(1004, 1, 'paystack', 'PayStack'),
(1005, 1, 'paytabs', 'PayTabs'),
(1006, 1, 'pay_now', 'Pay Now'),
(1007, 1, 'pay_wallet_balance_exp', 'Pay with your wallet balance'),
(1008, 1, 'pay_wallet_balance_warning', 'The order amount will be deducted from your wallet balance. If you approve, please click the button below to complete the purchase.'),
(1009, 1, 'pending', 'Pending'),
(1010, 1, 'pending_comments', 'Pending Comments'),
(1011, 1, 'pending_payment', 'Pending Payment'),
(1012, 1, 'pending_products', 'Pending Products'),
(1013, 1, 'pending_quote', 'Pending Quote'),
(1014, 1, 'permanently_rejected', 'Permanently Rejected'),
(1015, 1, 'permissions', 'Permissions'),
(1016, 1, 'personal_website_url', 'Personal Website URL'),
(1017, 1, 'phone', 'Phone'),
(1018, 1, 'phone_number', 'Phone Number'),
(1019, 1, 'physical', 'Physical'),
(1020, 1, 'physical_exp', 'A tangible product that you will ship to buyers'),
(1021, 1, 'physical_products', 'Physical Products'),
(1022, 1, 'pinterest_url', 'Pinterest URL'),
(1023, 1, 'place_order', 'Place Order'),
(1024, 1, 'plan_expiration_date', 'Plan Expiration Date'),
(1025, 1, 'play', 'Play'),
(1026, 1, 'please_wait', 'Please wait...'),
(1027, 1, 'popular', 'Popular'),
(1028, 1, 'postal_code', 'Postal Code'),
(1029, 1, 'postcode', 'Postcode'),
(1030, 1, 'posts', 'Posts'),
(1031, 1, 'post_comment', 'Post Comment'),
(1032, 1, 'preferences', 'Preferences'),
(1033, 1, 'preview', 'Preview'),
(1034, 1, 'price', 'Price'),
(1035, 1, 'price_per_day', 'Price Per Day'),
(1036, 1, 'price_per_month', 'Price Per Month'),
(1037, 1, 'pricing', 'Pricing'),
(1038, 1, 'primary', 'Primary'),
(1039, 1, 'print', 'Print'),
(1040, 1, 'priority', 'Priority'),
(1041, 1, 'priority_exp', 'The priority of a particular URL relative to other pages on the same site'),
(1042, 1, 'processing', 'Processing...'),
(1043, 1, 'product', 'Product'),
(1044, 1, 'production', 'Production'),
(1045, 1, 'products', 'Products'),
(1046, 1, 'products_ad_space', 'Products Ad Space'),
(1047, 1, 'products_by_category', 'Products by Category'),
(1048, 1, 'products_by_category_exp', 'Show products by categories on the homepage'),
(1049, 1, 'products_per_row_homepage', 'Products per Row on Homepage'),
(1050, 1, 'products_sent_different_stores', 'Your products will be sent by different stores.'),
(1051, 1, 'product_added', 'Product added successfully!'),
(1052, 1, 'product_added_to_cart', 'Product successfully added to your cart!'),
(1053, 1, 'product_ad_space', 'Product Ad Space'),
(1054, 1, 'product_approval_edited_products', 'Product Approval for Edited Products'),
(1055, 1, 'product_approval_new_products', 'Product Approval for New Products'),
(1056, 1, 'product_approve_published', 'Once it is approved, it will be published on the site.'),
(1057, 1, 'product_based_vat', 'Product Based VAT'),
(1058, 1, 'product_cache_system', 'Product Cache System'),
(1059, 1, 'product_cart_summary', 'Product cart summary'),
(1060, 1, 'product_code', 'Product Code'),
(1061, 1, 'product_comments', 'Product Comments'),
(1062, 1, 'product_details', 'Product Details'),
(1063, 1, 'product_does_not_ship_location', 'This product does not ship to this location.\r\n'),
(1064, 1, 'product_id', 'Product Id'),
(1065, 1, 'product_id_not_defined', 'Product ID is not defined.'),
(1066, 1, 'product_image_display_mode', 'Product Image Display Mode'),
(1067, 1, 'product_image_upload', 'Product Image Upload'),
(1068, 1, 'product_image_upload_limit', 'Product Image Upload Limit'),
(1069, 1, 'product_link_structure', 'Product Link Structure'),
(1070, 1, 'product_location', 'Product Location'),
(1071, 1, 'product_location_exp', 'Optional product location. Your shop location will be displayed if you do not add a location for your product'),
(1072, 1, 'product_location_system', 'Product Location System'),
(1073, 1, 'product_options', 'Product Options'),
(1074, 1, 'product_options_exp', 'Add available options, like color or size that buyers can choose during checkout'),
(1075, 1, 'product_or_seller_profile_url', 'Product or seller profile URL'),
(1076, 1, 'product_price', 'Product Price'),
(1077, 1, 'product_promoting_payment', 'Product Promotion Payment'),
(1078, 1, 'product_promotion', 'Product Promotion'),
(1079, 1, 'product_search_listing', 'Product Search & Listing'),
(1080, 1, 'product_settings', 'Product Settings'),
(1081, 1, 'product_type', 'Product Type'),
(1082, 1, 'product_url', 'Product URL'),
(1083, 1, 'profile', 'Profile'),
(1084, 1, 'profile_id', 'Profile Id'),
(1085, 1, 'profile_settings', 'Profile Settings'),
(1086, 1, 'program_type', 'Program Type'),
(1087, 1, 'promote', 'Promote'),
(1088, 1, 'promote_plan', 'Promote Plan'),
(1089, 1, 'promote_your_product', 'Promote Your Product'),
(1090, 1, 'promotion_payments', 'Promotion Payments'),
(1091, 1, 'public_coupon', 'Public Coupon'),
(1092, 1, 'public_coupon_exp', 'Public coupons are visible to all users'),
(1093, 1, 'public_key', 'Public Key'),
(1094, 1, 'public_url', 'Public URL'),
(1095, 1, 'publishable_key', 'Publishable Key'),
(1096, 1, 'purchase', 'Purchase'),
(1097, 1, 'purchased_plan', 'Purchased Plan'),
(1098, 1, 'purchase_code', 'Purchase Code'),
(1099, 1, 'pwa', 'Progressive Web App (PWA)'),
(1100, 1, 'pwa_logo', 'PWA Logo'),
(1101, 1, 'pwa_warning', 'If you enable PWA option, read \'Progressive Web App (PWA)\' section from the documentation to make the necessary settings.'),
(1102, 1, 'quantity', 'Quantity'),
(1103, 1, 'question', 'Question'),
(1104, 1, 'quote', 'Quote'),
(1105, 1, 'quote_request', 'Quote Request'),
(1106, 1, 'quote_requests', 'Quote Requests'),
(1107, 1, 'radio_buttons', 'Radio Buttons'),
(1108, 1, 'rate_this_product', 'Rate this product'),
(1109, 1, 'razorpay', 'Razorpay'),
(1110, 1, 'reason', 'Reason'),
(1111, 1, 'rebuild_category_paths', 'Rebuild Category Paths'),
(1112, 1, 'receipt', 'Receipt'),
(1113, 1, 'received_quote_requests', 'Received Quote Requests'),
(1114, 1, 'receiver', 'Receiver'),
(1115, 1, 'recent_chats', 'Recent Chats'),
(1116, 1, 'reference', 'Reference'),
(1117, 1, 'reference_code', 'Reference Code'),
(1118, 1, 'referral_discount', 'Referral Discount'),
(1119, 1, 'referral_earnings', 'Referral Earnings'),
(1120, 1, 'referrer_commission', 'Referrer Commission'),
(1121, 1, 'referrer_commission_rate', 'Referrer Commission Rate'),
(1122, 1, 'refresh', 'Refresh'),
(1123, 1, 'refresh_cache_database_changes', 'Refresh Cache Files When Database Changes'),
(1124, 1, 'refund', 'Refund'),
(1125, 1, 'refund_admin_complete_exp', 'To complete a refund request, you must return the buyer\'s money. If you click the \"Approve Refund\" button, the system will change the order status to \"Refund Approved\" and deduct the order amount from the seller\'s balance.'),
(1126, 1, 'refund_approved', 'Refund Approved'),
(1127, 1, 'refund_approved_exp', 'Your refund request has been approved by the seller. The total amount for this product will be refunded to you.'),
(1128, 1, 'refund_declined_exp', 'Your refund request has been declined by the seller. If you want to raise a dispute, you can contact the site management.'),
(1129, 1, 'refund_reason_explain', 'Why do you want a refund? Explain in detail.'),
(1130, 1, 'refund_request', 'Refund Request'),
(1131, 1, 'refund_requests', 'Refund Requests'),
(1132, 1, 'refund_system', 'Refund System'),
(1133, 1, 'regenerate', 'Regenerate'),
(1134, 1, 'region', 'Region'),
(1135, 1, 'regions', 'Regions'),
(1136, 1, 'register', 'Register'),
(1137, 1, 'register_with_email', 'Or register with email'),
(1138, 1, 'regular', 'Regular'),
(1139, 1, 'regular_listing', 'Regular Listing'),
(1140, 1, 'reject', 'Reject'),
(1141, 1, 'rejected', 'Rejected'),
(1142, 1, 'rejected_quote', 'Rejected Quote'),
(1143, 1, 'reject_permanently', 'Reject Permanently'),
(1144, 1, 'reject_quote', 'Reject Quote'),
(1145, 1, 'related_help_topics', 'Related Help Topics'),
(1146, 1, 'related_posts', 'Related Posts'),
(1147, 1, 'remaining_days', 'Remaining Days'),
(1148, 1, 'remove', 'Remove'),
(1149, 1, 'removed_from_affiliate_program', 'You have been removed from the affiliate program.'),
(1150, 1, 'remove_from_featured', 'Remove from Featured'),
(1151, 1, 'remove_from_product_filters', 'Remove from Product Filters'),
(1152, 1, 'remove_from_special_offers', 'Remove from Special Offers'),
(1153, 1, 'remove_from_wishlist', 'Remove from wishlist'),
(1154, 1, 'remove_user_ban', 'Remove User Ban'),
(1155, 1, 'renew_your_plan', 'Renew Your Plan'),
(1156, 1, 'reply', 'Reply'),
(1157, 1, 'reply_to', 'Reply to'),
(1158, 1, 'report', 'Report'),
(1159, 1, 'reported_content', 'Reported Content'),
(1160, 1, 'report_bank_transfer', 'Report Bank Transfer'),
(1161, 1, 'report_comment', 'Report Comment'),
(1162, 1, 'report_review', 'Report Review'),
(1163, 1, 'report_this_product', 'Report this product'),
(1164, 1, 'report_this_seller', 'Report this seller'),
(1165, 1, 'report_type', 'Report Type'),
(1166, 1, 'request_a_quote', 'Request a Quote'),
(1167, 1, 'request_documents_vendors', 'Request Documents from Vendors to Open a Store'),
(1168, 1, 'required', 'Required'),
(1169, 1, 'required_files', 'Required Files'),
(1170, 1, 'resend_activation_email', 'Resend Activation Email'),
(1171, 1, 'reset', 'Reset'),
(1172, 1, 'reset_cache', 'Reset Cache'),
(1173, 1, 'reset_filters', 'Reset Filters'),
(1174, 1, 'reset_location', 'Reset Location'),
(1175, 1, 'reset_password', 'Reset Password'),
(1176, 1, 'reset_password_subtitle', 'Enter your email address'),
(1177, 1, 'responded', 'Responded'),
(1178, 1, 'restore', 'Restore'),
(1179, 1, 'return_to_cart', 'Return to cart'),
(1180, 1, 'review', 'Review'),
(1181, 1, 'reviews', 'Reviews'),
(1182, 1, 'right', 'Right'),
(1183, 1, 'right_to_left', 'Right to Left (RTL)'),
(1184, 1, 'role', 'Role'),
(1185, 1, 'roles', 'Roles'),
(1186, 1, 'roles_permissions', 'Roles & Permissions'),
(1187, 1, 'role_name', 'Role Name'),
(1188, 1, 'round_boxes', 'Round Boxes'),
(1189, 1, 'route_settings', 'Route Settings'),
(1190, 1, 'route_settings_warning', 'You cannot use special characters in routes. If your language contains special characters, please be careful when editing routes. If you enter an invalid route, you will not be able to access the related page.'),
(1191, 1, 'rss_feeds', 'RSS Feeds'),
(1192, 1, 'rss_system', 'RSS System'),
(1193, 1, 'sale', 'Sale'),
(1194, 1, 'sales', 'Sales'),
(1195, 1, 'sales_number', 'Sales'),
(1196, 1, 'sale_id', 'Sale Id'),
(1197, 1, 'sandbox', 'Sandbox'),
(1198, 1, 'saturday', 'Saturday'),
(1199, 1, 'save_and_continue', 'Save and Continue'),
(1200, 1, 'save_as_draft', 'Save as Draft'),
(1201, 1, 'save_changes', 'Save Changes'),
(1202, 1, 'search', 'Search'),
(1203, 1, 'searching', 'Searching...'),
(1204, 1, 'search_by_location', 'Search by Location'),
(1205, 1, 'search_products_categories_brands', 'Search for products, categories or brands'),
(1206, 1, 'search_results', 'Search Results'),
(1207, 1, 'secret_key', 'Secret Key'),
(1208, 1, 'secure_key', 'Secure Key'),
(1209, 1, 'see_details', 'See Details'),
(1210, 1, 'see_more', 'See more'),
(1211, 1, 'see_order_details', 'See Order Details'),
(1212, 1, 'see_products', 'See Products'),
(1213, 1, 'select', 'Select'),
(1214, 1, 'selected_images', 'Selected Images'),
(1215, 1, 'select_action', 'Select Action'),
(1216, 1, 'select_ad_space', 'Select Ad Space'),
(1217, 1, 'select_all', 'Select All'),
(1218, 1, 'select_category', 'Select Category'),
(1219, 1, 'select_chat_start_messaging', 'Select a chat to start messaging'),
(1220, 1, 'select_country', 'Select Country'),
(1221, 1, 'select_favicon', 'Select Favicon'),
(1222, 1, 'select_file', 'Select File'),
(1223, 1, 'select_for_coupon', 'Select for Coupon'),
(1224, 1, 'select_image', 'Select Image'),
(1225, 1, 'select_language', 'Select Language'),
(1226, 1, 'select_location', 'Select Location'),
(1227, 1, 'select_logo', 'Select Logo'),
(1228, 1, 'select_option', 'Select an option'),
(1229, 1, 'select_payment_option', 'Select Payment Option'),
(1230, 1, 'select_products', 'Select Products'),
(1231, 1, 'select_region', 'Select Region'),
(1232, 1, 'select_your_location', 'Select Your Location'),
(1233, 1, 'select_your_plan', 'Select Your Plan'),
(1234, 1, 'select_your_plan_exp', 'Select your membership plan to continue'),
(1235, 1, 'seller', 'Seller'),
(1236, 1, 'sellers_bid', 'Seller\'s Bid'),
(1237, 1, 'seller_balances', 'Seller Balances'),
(1238, 1, 'seller_does_not_ship_to_address', 'This seller does not ship to the address you have chosen. You can continue by removing the products of this seller from your cart.'),
(1239, 1, 'selling_license_keys', 'Selling License Keys'),
(1240, 1, 'selling_on_the_site', 'Selling on the Site'),
(1241, 1, 'sell_my_product_on_site', 'Sell product on the site'),
(1242, 1, 'sell_now', 'Sell Now'),
(1243, 1, 'send', 'Send'),
(1244, 1, 'sender', 'Sender'),
(1245, 1, 'sender_email_address', 'Sender Email Address'),
(1246, 1, 'send_email', 'Send Email'),
(1247, 1, 'send_email_to_buyer', 'Send Email to Buyer'),
(1248, 1, 'send_message', 'Send Message'),
(1249, 1, 'send_test_email', 'Send Test Email'),
(1250, 1, 'send_test_email_exp', 'You can send a test mail to check if your mail server is working.'),
(1251, 1, 'sent_by', 'Sent By'),
(1252, 1, 'seo', 'SEO'),
(1253, 1, 'seo_metadata', 'SEO & Metadata'),
(1254, 1, 'seo_tools', 'SEO Tools'),
(1255, 1, 'september', 'September'),
(1256, 1, 'server_key', 'Server Key'),
(1257, 1, 'server_response', 'Server\'s Response'),
(1258, 1, 'settings', 'Settings'),
(1259, 1, 'settings_language', 'Settings Language'),
(1260, 1, 'set_as_default', 'Set as Default'),
(1261, 1, 'set_fixed_vat_rate_all_countries', 'Set Fixed VAT Rate for All Countries'),
(1262, 1, 'set_payout_account', 'Set Payout Account'),
(1263, 1, 'share', 'Share'),
(1264, 1, 'shipped', 'Shipped'),
(1265, 1, 'shipped_product', 'Shipped Product'),
(1266, 1, 'shipping', 'Shipping'),
(1267, 1, 'shipping_address', 'Shipping Address'),
(1268, 1, 'shipping_by_per_item', 'Fixed Price Per Item'),
(1269, 1, 'shipping_by_per_order', 'Fixed Price Per Order'),
(1270, 1, 'shipping_by_total_weight', 'Calculate by Total Weight'),
(1271, 1, 'shipping_calculation_type', 'Shipping Calculation Type'),
(1272, 1, 'shipping_cost', 'Shipping Cost'),
(1273, 1, 'shipping_cost_calculation_exp', 'This cost will be applied based on your selection (per order or per item).'),
(1274, 1, 'shipping_delivery_times', 'Shipping Delivery Times'),
(1275, 1, 'shipping_delivery_times_exp', 'You can add shipping delivery times from here (E.g: Ready to ship in 1 Business Day)'),
(1276, 1, 'shipping_information', 'Shipping Information'),
(1277, 1, 'shipping_location', 'Shipping & Location'),
(1278, 1, 'shipping_method', 'Shipping Method'),
(1279, 1, 'shipping_methods', 'Shipping Methods'),
(1280, 1, 'shipping_method_exp', 'To use this shipping zone, you need to add at least one shipping method'),
(1281, 1, 'shipping_settings', 'Shipping Settings'),
(1282, 1, 'shipping_time', 'Shipping Time'),
(1283, 1, 'shipping_zones', 'Shipping Zones '),
(1284, 1, 'shop', 'Shop'),
(1285, 1, 'shopping_cart', 'Shopping Cart'),
(1286, 1, 'shop_by_brand', 'Shop By Brand'),
(1287, 1, 'shop_by_category', 'Shop By Category'),
(1288, 1, 'shop_description', 'Shop Description'),
(1289, 1, 'shop_id', 'Shop ID'),
(1290, 1, 'shop_location', 'Shop Location'),
(1291, 1, 'shop_name', 'Shop Name'),
(1292, 1, 'shop_now', 'Shop Now'),
(1293, 1, 'shop_opening_request', 'Shop Opening Request'),
(1294, 1, 'shop_opening_requests', 'Shop Opening Requests'),
(1295, 1, 'shop_opening_request_emails', 'Shop opening request emails'),
(1296, 1, 'shop_policies', 'Shop Policies'),
(1297, 1, 'shop_settings', 'Shop Settings'),
(1298, 1, 'short', 'Short'),
(1299, 1, 'short_description', 'Short Description'),
(1300, 1, 'short_form', 'Short Form'),
(1301, 1, 'show', 'Show'),
(1302, 1, 'show_all', 'Show All'),
(1303, 1, 'show_breadcrumb', 'Show Breadcrumb'),
(1304, 1, 'show_category_image_on_menu', 'Show Category Image on Menu'),
(1305, 1, 'show_cookies_warning', 'Show Cookies Warning'),
(1306, 1, 'show_customer_email_seller', 'Show Customer Email to Seller'),
(1307, 1, 'show_customer_phone_number_seller', 'Show Customer Phone Number to Seller'),
(1308, 1, 'show_description_category_page', 'Show Description on Category Page'),
(1309, 1, 'show_featured_products_first_search', 'Show Featured Products First in Search Results'),
(1310, 1, 'show_first_search_lists', 'Show first in search lists'),
(1311, 1, 'show_image_on_main_menu', 'Show Image on Main Menu'),
(1312, 1, 'show_image_on_navigation', 'Show Category Image on the Navigation'),
(1313, 1, 'show_my_email', 'Show my email address'),
(1314, 1, 'show_my_location', 'Show my location'),
(1315, 1, 'show_my_phone', 'Show my phone number'),
(1316, 1, 'show_number_sales_profile', 'Show Number of Sales on Profile'),
(1317, 1, 'show_on_main_menu', 'Show on Main Menu'),
(1318, 1, 'show_on_slider', 'Show on Slider'),
(1319, 1, 'show_previous_products', 'Show Previous Products'),
(1320, 1, 'show_reason', 'Show Reason'),
(1321, 1, 'show_right_column', 'Show Right Column'),
(1322, 1, 'show_sold_products_on_site', 'Show Sold Products on the Site'),
(1323, 1, 'show_subcategory_products', 'Show subcategory products'),
(1324, 1, 'show_title', 'Show Title'),
(1325, 1, 'show_under_these_categories', 'Custom field will be displayed under these categories'),
(1326, 1, 'show_vendor_contact_information', 'Show Vendor Contact Information on the Site'),
(1327, 1, 'show_vendor_contact_info_guests', 'Show Vendor Contact Information to Guests'),
(1328, 1, 'single_country_mode', 'Single Country Mode'),
(1329, 1, 'single_select', 'Single Select'),
(1330, 1, 'sitemap', 'Sitemap'),
(1331, 1, 'site_description', 'Site Description'),
(1332, 1, 'site_font', 'Site Font'),
(1333, 1, 'site_key', 'Site Key'),
(1334, 1, 'site_title', 'Site Title'),
(1335, 1, 'skip', 'Skip'),
(1336, 1, 'sku', 'SKU'),
(1337, 1, 'slide', 'Slide'),
(1338, 1, 'slider', 'Slider'),
(1339, 1, 'slider_items', 'Slider Items'),
(1340, 1, 'slider_settings', 'Slider Settings'),
(1341, 1, 'slug', 'Slug'),
(1342, 1, 'slug_exp', 'If you leave it empty, it will be generated automatically.'),
(1343, 1, 'smtp', 'SMTP'),
(1344, 1, 'social_login', 'Social Login'),
(1345, 1, 'social_media', 'Social Media'),
(1346, 1, 'social_media_settings', 'Social Media Settings'),
(1347, 1, 'sold', 'Sold'),
(1348, 1, 'sold_products', 'Sold Products'),
(1349, 1, 'sort_categories', 'Sort Categories'),
(1350, 1, 'sort_options', 'Sort Options'),
(1351, 1, 'sort_parent_categories_by_category_order', 'Sort Parent Categories by Category Order'),
(1352, 1, 'special_offer', 'Special Offer'),
(1353, 1, 'special_offers', 'Special Offers'),
(1354, 1, 'start', 'Start'),
(1355, 1, 'start_selling', 'Start Selling'),
(1356, 1, 'start_selling_exp', 'In order to sell your products, you must be a verified member. Verification is a one-time process. This verification process is necessary because of spammers and fraud.'),
(1357, 1, 'state', 'State'),
(1358, 1, 'states', 'States'),
(1359, 1, 'static_cache_system', 'Static Cache System'),
(1360, 1, 'static_cache_system_exp', 'While the cache system is used for products that are updated more frequently, static cache is applied to records that do not change often (such as categories, custom fields, settings, etc.). If any changes occur in these records, the cache files are automatically refreshed.'),
(1361, 1, 'static_content_cache', 'Static Content Cache'),
(1362, 1, 'status', 'Status'),
(1363, 1, 'still_have_questions', 'Still have questions?'),
(1364, 1, 'still_have_questions_exp', 'If you still have a question, you can submit a support request here.'),
(1365, 1, 'stock', 'Stock'),
(1366, 1, 'storage', 'Storage'),
(1367, 1, 'store_name', 'Store Name'),
(1368, 1, 'stripe', 'Stripe'),
(1369, 1, 'stripe_checkout', 'Stripe Checkout'),
(1370, 1, 'stripe_payment_for', 'Promote payment for'),
(1371, 1, 'subcategory', 'Subcategory'),
(1372, 1, 'subject', 'Subject'),
(1373, 1, 'submit', 'Submit'),
(1374, 1, 'submit_a_new_quote', 'Submit a New Quote'),
(1375, 1, 'submit_a_quote', 'Submit a Quote'),
(1376, 1, 'submit_a_request', 'Submit a Request'),
(1377, 1, 'submit_refund_request', 'Submit a Refund Request'),
(1378, 1, 'subscribe', 'Subscribe'),
(1379, 1, 'subscribers', 'Subscribers'),
(1380, 1, 'subtotal', 'Subtotal'),
(1381, 1, 'succeeded', 'Succeeded'),
(1382, 1, 'summary', 'Summary'),
(1383, 1, 'sunday', 'Sunday'),
(1384, 1, 'supported_currencies', 'Supported Currencies'),
(1385, 1, 'support_system_emails', 'Support system emails'),
(1386, 1, 'support_ticket', 'Ticket'),
(1387, 1, 'support_tickets', 'Support Tickets'),
(1388, 1, 'swatch_color', 'Swatch - Color'),
(1389, 1, 'swatch_image', 'Swatch - Image'),
(1390, 1, 'swift', 'SWIFT'),
(1391, 1, 'swift_code', 'SWIFT Code'),
(1392, 1, 'swift_iban', 'Bank Account Number/IBAN'),
(1393, 1, 'system', 'System'),
(1394, 1, 'tag', 'Tag'),
(1395, 1, 'tags', 'Tags'),
(1396, 1, 'tags_product_exp', 'Add relevant keywords for your product to increase visibility in search results'),
(1397, 1, 'tax_name', 'Tax Name'),
(1398, 1, 'tax_rate', 'Tax Rate'),
(1399, 1, 'tax_registration_number', 'Tax Registration Number'),
(1400, 1, 'tax_settings', 'Tax Settings'),
(1401, 1, 'telegram_url', 'Telegram URL'),
(1402, 1, 'tell_us_about_shop', 'Tell Us About Your Shop'),
(1403, 1, 'terms_conditions_exp', 'I have read and agree to the'),
(1404, 1, 'text', 'Text'),
(1405, 1, 'textarea', 'Textarea'),
(1406, 1, 'text_color', 'Text Color'),
(1407, 1, 'text_direction', 'Text Direction'),
(1408, 1, 'text_editor_language', 'Text Editor Language'),
(1409, 1, 'text_input', 'Text Input'),
(1410, 1, 'theme', 'Theme'),
(1411, 1, 'there_is_shop_opening_request', 'There is a new shop opening request.'),
(1412, 1, 'the_operation_completed', 'The operation completed successfully!'),
(1413, 1, 'thursday', 'Thursday'),
(1414, 1, 'ticket', 'Ticket'),
(1415, 1, 'tiktok_url', 'TikTok URL'),
(1416, 1, 'timezone', 'Timezone'),
(1417, 1, 'time_limit_for_plan', 'A time limit for the plan'),
(1418, 1, 'title', 'Title'),
(1419, 1, 'to', 'To:'),
(1420, 1, 'token', 'Token'),
(1421, 1, 'tone_academic', 'Academic'),
(1422, 1, 'tone_casual', 'Casual'),
(1423, 1, 'tone_critical', 'Critical'),
(1424, 1, 'tone_formal', 'Formal'),
(1425, 1, 'tone_humorous', 'Humorous'),
(1426, 1, 'tone_inspirational', 'Inspirational'),
(1427, 1, 'tone_persuasive', 'Persuasive'),
(1428, 1, 'tone_professional', 'Professional'),
(1429, 1, 'tone_style', 'Tone/Style'),
(1430, 1, 'too_many_attempts', 'Too many login attempts. Please try again after 5 minutes.'),
(1431, 1, 'top', 'Top'),
(1432, 1, 'topic', 'Topic'),
(1433, 1, 'top_menu', 'Top Menu'),
(1434, 1, 'total', 'Total'),
(1435, 1, 'total_amount', 'Total Amount:'),
(1436, 1, 'tracking_code', 'Tracking Code'),
(1437, 1, 'tracking_url', 'Tracking URL'),
(1438, 1, 'transactions', 'Transactions'),
(1439, 1, 'transaction_fee', 'Transaction Fee'),
(1440, 1, 'transaction_fee_exp', 'If you don\'t want to charge a transaction fee, type 0'),
(1441, 1, 'transaction_number', 'Transaction Number'),
(1442, 1, 'translation', 'Translation'),
(1443, 1, 'tuesday', 'Tuesday'),
(1444, 1, 'twitch_url', 'Twitch Url'),
(1445, 1, 'twitter_url', 'X (Twitter) URL'),
(1446, 1, 'type', 'Type'),
(1447, 1, 'type_extension', 'Type an extension and hit enter '),
(1448, 1, 'type_tag', 'Type tag and hit enter'),
(1449, 1, 'unconfirmed', 'Unconfirmed'),
(1450, 1, 'unfollow', 'Unfollow'),
(1451, 1, 'unit_price', 'Unit Price'),
(1452, 1, 'unlimited', 'Unlimited'),
(1453, 1, 'unlimited_stock', 'Unlimited Stock'),
(1454, 1, 'unsubscribe', 'Unsubscribe'),
(1455, 1, 'unsubscribe_successful', 'Unsubscribe Successful!'),
(1456, 1, 'updated', 'Updated'),
(1457, 1, 'update_category', 'Update Category'),
(1458, 1, 'update_city', 'Update City'),
(1459, 1, 'update_country', 'Update Country'),
(1460, 1, 'update_currency', 'Update Currency'),
(1461, 1, 'update_custom_field', 'Update Custom Field'),
(1462, 1, 'update_exchange_rates', 'Update Exchange Rates'),
(1463, 1, 'update_font', 'Update Font'),
(1464, 1, 'update_language', 'Update Language'),
(1465, 1, 'update_order_status', 'Update Order Status'),
(1466, 1, 'update_page', 'Update Page'),
(1467, 1, 'update_post', 'Update Post'),
(1468, 1, 'update_product', 'Update Product'),
(1469, 1, 'update_profile', 'Update Profile'),
(1470, 1, 'update_quote', 'Update Quote'),
(1471, 1, 'update_seller_balance', 'Update Seller Balance'),
(1472, 1, 'update_slider_item', 'Update Slider Item'),
(1473, 1, 'update_state', 'Update State'),
(1474, 1, 'upload', 'Upload'),
(1475, 1, 'uploaded', 'Uploaded'),
(1476, 1, 'uploading', 'Uploading...'),
(1477, 1, 'upload_file', 'Upload File'),
(1478, 1, 'upload_your_banner', 'Upload Your Banner'),
(1479, 1, 'url', 'URL'),
(1480, 1, 'used', 'Used'),
(1481, 1, 'user', 'User'),
(1482, 1, 'username', 'Username'),
(1483, 1, 'users', 'Users'),
(1484, 1, 'user_agent', 'User Agent'),
(1485, 1, 'user_details', 'User Details'),
(1486, 1, 'user_id', 'User Id'),
(1487, 1, 'user_login_activities', 'User Login Activities'),
(1488, 1, 'user_reviews', 'User Reviews'),
(1489, 1, 'use_default_payment_account', 'Use as default payment account'),
(1490, 1, 'use_same_address_for_billing', 'Use same address for billing address'),
(1491, 1, 'use_text', 'Use Text'),
(1492, 1, 'use_this_date', 'Use This Date'),
(1493, 1, 'vacation_message', 'Vacation Message'),
(1494, 1, 'vacation_mode', 'Vacation Mode'),
(1495, 1, 'value', 'Value'),
(1496, 1, 'variants', 'Variants'),
(1497, 1, 'variant_name', 'Variant Name'),
(1498, 1, 'vat', 'VAT'),
(1499, 1, 'vat_exp', 'Value-Added Tax'),
(1500, 1, 'vat_vendor_dashboard_exp', 'Define VAT values for your products based on countries'),
(1501, 1, 'vat_vendor_exp', 'Allow vendors to add VAT for their products'),
(1502, 1, 'vendor', 'Vendor'),
(1503, 1, 'vendors', 'Vendors'),
(1504, 1, 'vendor_bulk_product_upload', 'Vendor Bulk Product Upload'),
(1505, 1, 'vendor_no_shipping_option_warning', 'If you want to sell a physical product, you must add your shipping options before adding the product. Please go to this section and add your shipping options:'),
(1506, 1, 'vendor_on_vacation', 'Vendor on Vacation'),
(1507, 1, 'vendor_on_vacation_exp', 'This vendor is currently on vacation and is not available to process orders or respond to messages.'),
(1508, 1, 'vendor_on_vacation_vendor_exp', 'Vacation mode allows you to pause your store for a certain period of time'),
(1509, 1, 'vendor_vat_rates_exp', 'The VAT rate you set for a country will apply to all states within that country. However, if you want a state to have its own unique tax rate, you can specify a different VAT rate for that state.'),
(1510, 1, 'vendor_verification_system', 'Vendor Verification System'),
(1511, 1, 'vendor_verification_system_exp', 'Disable if you want to allow all users to add products.'),
(1512, 1, 'vertical_alignment', 'Vertical Alignment'),
(1513, 1, 'very_long', 'Very Long'),
(1514, 1, 'very_short', 'Very Short'),
(1515, 1, 'video', 'Video'),
(1516, 1, 'video_preview', 'Video Preview'),
(1517, 1, 'video_preview_exp', 'MP4 or WEBM preview video'),
(1518, 1, 'view_all', 'View All'),
(1519, 1, 'view_cart', 'View Cart'),
(1520, 1, 'view_content', 'View Content'),
(1521, 1, 'view_details', 'View Details'),
(1522, 1, 'view_invoice', 'View Invoice'),
(1523, 1, 'view_license_keys', 'View License Keys'),
(1524, 1, 'view_options', 'View Options'),
(1525, 1, 'view_pdf_file', 'View PDF File'),
(1526, 1, 'view_product', 'View Product'),
(1527, 1, 'view_site', 'View Site'),
(1528, 1, 'visibility', 'Visibility'),
(1529, 1, 'visible', 'Visible'),
(1530, 1, 'visual_settings', 'Visual Settings'),
(1531, 1, 'vk_login', 'VKontakte Login'),
(1532, 1, 'vk_url', 'VK URL'),
(1533, 1, 'waiting', 'Waiting...'),
(1534, 1, 'wallet', 'Wallet'),
(1535, 1, 'wallet_balance', 'Wallet Balance'),
(1536, 1, 'wallet_deposit', 'Wallet Deposit'),
(1537, 1, 'wallet_deposits', 'Wallet Deposits'),
(1538, 1, 'warning', 'Warning'),
(1539, 1, 'warning_add_order_tracking_code', 'You can add the order tracking code and link while changing the order status.'),
(1540, 1, 'warning_cannot_choose_plan', 'You cannot choose this plan due to the number of products you have added'),
(1541, 1, 'warning_category_sort', 'Sorting with drag and drop will be active only when the \"by Category Order\" option is selected.'),
(1542, 1, 'warning_custom_field_category', 'The selected custom field will automatically apply to all subcategories of the chosen category. Therefore, there is no need to select subcategories individually.'),
(1543, 1, 'warning_edit_profile_image', 'Click on the save changes button after selecting your image'),
(1544, 1, 'warning_external_download_link', 'For security reasons, it is recommended to upload digital files instead of adding an external download link'),
(1545, 1, 'warning_membership_admin_role', 'Admin role does not require a membership plan.'),
(1546, 1, 'warning_plan_used', 'This plan has been used before'),
(1547, 1, 'warning_product_main_image', 'You can click on the \"Main\" button on the images to select the main image of your product'),
(1548, 1, 'watermark', 'Watermark'),
(1549, 1, 'watermark_image', 'Watermark Image'),
(1550, 1, 'watermark_text', 'Watermark Text'),
(1551, 1, 'webhook_secret', 'Webhook Secret'),
(1552, 1, 'wednesday', 'Wednesday'),
(1553, 1, 'weekly', 'Weekly'),
(1554, 1, 'weight', 'Weight'),
(1555, 1, 'whatsapp_url', 'WhatsApp URL'),
(1556, 1, 'where_to_display', 'Where to Display'),
(1557, 1, 'width', 'Width'),
(1558, 1, 'wishlist', 'Wishlist'),
(1559, 1, 'withdraw_amount', 'Withdrawal Amount'),
(1560, 1, 'withdraw_method', 'Withdrawal Method'),
(1561, 1, 'withdraw_money', 'Withdraw Money'),
(1562, 1, 'write_a_message', 'Write a message...'),
(1563, 1, 'write_review', 'Write a Review...'),
(1564, 1, 'wrong_password', 'Wrong password!'),
(1565, 1, 'years_ago', 'years ago'),
(1566, 1, 'year_ago', 'year ago'),
(1567, 1, 'yes', 'Yes'),
(1568, 1, 'yoomoney', 'YooMoney'),
(1569, 1, 'your_balance', 'Your Balance'),
(1570, 1, 'your_cart_is_empty', 'Your cart is empty!'),
(1571, 1, 'your_order_shipped', 'Your order has been shipped'),
(1572, 1, 'your_quote_accepted', 'Your quote has been accepted.'),
(1573, 1, 'your_quote_rejected', 'Your quote has been rejected.'),
(1574, 1, 'your_quote_request_replied', 'Your quote request has been replied.'),
(1575, 1, 'your_rating', 'Your Rating:'),
(1576, 1, 'your_shop_opening_request_approved', 'Your shop opening request has been approved. You can go to our site and start to sell your items!'),
(1577, 1, 'youtube_url', 'Youtube URL'),
(1578, 1, 'you_have_new_message', 'You have a new message'),
(1579, 1, 'you_have_new_order', 'You have a new order'),
(1580, 1, 'you_have_new_quote_request', 'You have a new quote request.'),
(1581, 1, 'you_have_no_debt', 'You have no debt.'),
(1582, 1, 'you_may_also_like', 'You may also like'),
(1583, 1, 'you_will_earn', 'You Will Earn'),
(1584, 1, 'zip_code', 'Zip Code'),
(1585, 1, 'zone_name', 'Zone Name');
(1586, 1, 'checkout_success', 'Checkout Success');


-- --------------------------------------------------------

--
-- Table structure for table `location_cities`
--

CREATE TABLE `location_cities` (
  `id` int NOT NULL,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `country_id` int NOT NULL,
  `state_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `location_countries`
--

CREATE TABLE `location_countries` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `continent_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `location_states`
--

CREATE TABLE `location_states` (
  `id` int NOT NULL,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `country_id` smallint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `media_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membership_plans`
--

CREATE TABLE `membership_plans` (
  `id` int NOT NULL,
  `title_array` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `number_of_ads` int DEFAULT NULL,
  `number_of_days` int DEFAULT NULL,
  `price` decimal(12,2) DEFAULT '0.00',
  `is_free` tinyint(1) DEFAULT '0',
  `is_unlimited_number_of_ads` tinyint(1) DEFAULT '0',
  `is_unlimited_time` tinyint(1) DEFAULT '0',
  `features_array` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `plan_order` smallint DEFAULT '1',
  `is_popular` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membership_transactions`
--

CREATE TABLE `membership_transactions` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `plan_id` int DEFAULT NULL,
  `plan_title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_method` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_amount` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `global_taxes_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `payment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `checkout_token` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `order_number` bigint DEFAULT NULL,
  `buyer_id` int NOT NULL,
  `buyer_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price_subtotal` decimal(12,2) DEFAULT '0.00',
  `price_vat` decimal(12,2) DEFAULT '0.00',
  `price_shipping` decimal(12,2) DEFAULT '0.00',
  `price_total` decimal(12,2) DEFAULT '0.00',
  `price_currency` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `coupon_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `coupon_products` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `coupon_discount` decimal(12,2) DEFAULT '0.00',
  `coupon_discount_rate` smallint DEFAULT '0',
  `coupon_seller_id` int DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `transaction_fee_rate` double DEFAULT NULL,
  `transaction_fee` decimal(12,2) DEFAULT '0.00',
  `global_taxes_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shipping` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `affiliate_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `bank_transaction_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `checkout_token` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` bigint NOT NULL,
  `seller_id` int DEFAULT NULL,
  `buyer_id` int DEFAULT NULL,
  `buyer_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `product_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'physical',
  `listing_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_unit_price` decimal(12,2) DEFAULT '0.00',
  `product_quantity` int DEFAULT NULL,
  `product_currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_vat_rate` double DEFAULT '0',
  `product_vat` decimal(12,2) DEFAULT '0.00',
  `product_total_price` decimal(12,2) DEFAULT '0.00',
  `image_id` int DEFAULT NULL,
  `image_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `variation_option_ids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_options_snapshot` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `product_options_summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `commission_rate` double DEFAULT NULL,
  `order_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT '0',
  `shipping_tracking_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shipping_tracking_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shipping_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `seller_shipping_cost` decimal(12,2) DEFAULT '0.00',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT '1',
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `slug` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keywords` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `page_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `page_order` int DEFAULT '1',
  `visibility` tinyint(1) DEFAULT '1',
  `title_active` tinyint(1) DEFAULT '1',
  `location` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'information',
  `is_custom` tinyint(1) NOT NULL DEFAULT '1',
  `page_default_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `lang_id`, `title`, `slug`, `description`, `keywords`, `page_content`, `page_order`, `visibility`, `title_active`, `location`, `is_custom`, `page_default_name`, `created_at`) VALUES
(1, 1, 'Terms & Conditions', 'terms-conditions', 'Terms & Conditions Page', 'Terms, Conditions, Page', NULL, 1, 1, 1, 'information', 0, 'terms_conditions', '2025-08-24 07:40:30'),
(2, 1, 'Contact', 'contact', 'Contact Page', 'Contact, Page', NULL, 1, 1, 1, 'top_menu', 0, 'contact', '2025-08-24 07:40:30'),
(3, 1, 'Blog', 'blog', 'Blog Page', 'Blog, Page', NULL, 1, 1, 1, 'quick_links', 0, 'blog', '2025-08-24 07:40:30'),
(4, 1, 'Shops', 'shops', 'Shops Page', 'shops, page', NULL, 1, 1, 1, 'quick_links', 0, 'shops', '2025-08-24 07:40:30');

-- --------------------------------------------------------

--
-- Table structure for table `pages_vendor`
--

CREATE TABLE `pages_vendor` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `content_shop_policies` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `status_shop_policies` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateways`
--

CREATE TABLE `payment_gateways` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `public_key` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `secret_key` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `webhook_secret` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `environment` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'production',
  `transaction_fee` decimal(12,2) DEFAULT '0.00',
  `status` tinyint(1) DEFAULT '0',
  `logos` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_gateways`
--

INSERT INTO `payment_gateways` (`id`, `name`, `name_key`, `public_key`, `secret_key`, `webhook_secret`, `environment`, `transaction_fee`, `status`, `logos`) VALUES
(1, 'PayPal', 'paypal', NULL, NULL, NULL, 'sandbox', 0.00, 0, 'visa,mastercard,amex,discover,paypal'),
(2, 'Stripe', 'stripe', NULL, NULL, NULL, 'production', 0.00, 0, 'visa,mastercard,amex,discover,jcb,diners,stripe'),
(3, 'Paystack', 'paystack', NULL, NULL, NULL, 'production', 0.00, 0, 'visa,mastercard,verve,paystack'),
(4, 'Razorpay', 'razorpay', NULL, NULL, NULL, 'production', 0.00, 0, 'visa,mastercard,amex,maestro,diners,rupay,razorpay'),
(5, 'Flutterwave', 'flutterwave', NULL, NULL, NULL, 'production', 0.00, 0, 'visa,mastercard,amex,maestro,flutterwave'),
(6, 'Iyzico', 'iyzico', NULL, NULL, NULL, 'sandbox', 0.00, 0, 'visa,mastercard,amex,troy,iyzico'),
(7, 'Midtrans', 'midtrans', NULL, NULL, NULL, 'sandbox', 0.00, 0, 'visa,mastercard,amex,jcb,midtrans'),
(8, 'dLocal Go', 'dlocalgo', NULL, NULL, NULL, 'sandbox', 0.00, 0, 'visa,mastercard,amex,discover,boleto,d-local-go'),
(9, 'PayTabs', 'paytabs', NULL, NULL, NULL, 'production', 0.00, 0, 'visa,mastercard,amex,discover,paytabs'),
(10, 'YooMoney', 'yoomoney', NULL, NULL, NULL, 'sandbox', 0.00, 0, 'visa,mastercard,maestro,mir,yoomoney'),
(11, 'Mercado Pago', 'mercado_pago', NULL, NULL, NULL, 'production', 0.00, 0, 'visa,mastercard,amex,discover,boleto,mercado_pago');

-- --------------------------------------------------------

--
-- Table structure for table `payment_settings`
--

CREATE TABLE `payment_settings` (
  `id` int NOT NULL,
  `default_currency` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'USD',
  `allow_all_currencies_for_classied` tinyint(1) DEFAULT '1',
  `currency_converter` tinyint(1) DEFAULT '0',
  `auto_update_exchange_rates` tinyint(1) DEFAULT '1',
  `currency_converter_api` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `currency_converter_api_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_transfer_enabled` tinyint(1) DEFAULT '0',
  `bank_transfer_accounts` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `cash_on_delivery_enabled` tinyint(1) DEFAULT '1',
  `cash_on_delivery_debt_limit` decimal(12,2) DEFAULT '0.00',
  `price_per_day` decimal(12,2) DEFAULT '0.00',
  `price_per_month` decimal(12,2) DEFAULT '0.00',
  `free_product_promotion` tinyint(1) DEFAULT '0',
  `payout_paypal_enabled` tinyint(1) DEFAULT '1',
  `payout_bitcoin_enabled` tinyint(1) DEFAULT '0',
  `payout_iban_enabled` tinyint(1) DEFAULT '1',
  `payout_swift_enabled` tinyint(1) DEFAULT '1',
  `min_payout_paypal` decimal(12,2) DEFAULT '0.00',
  `min_payout_bitcoin` decimal(12,2) DEFAULT '0.00',
  `min_payout_iban` decimal(12,2) DEFAULT '0.00',
  `min_payout_swift` decimal(12,2) DEFAULT '0.00',
  `commission_rate` double DEFAULT '0',
  `vat_status` tinyint(1) DEFAULT '1',
  `wallet_status` tinyint(1) DEFAULT '1',
  `wallet_deposit` tinyint(1) DEFAULT '1',
  `wallet_min_deposit` decimal(12,2) DEFAULT '0.00',
  `pay_with_wallet_balance` tinyint(1) DEFAULT '1',
  `additional_invoice_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `cart_location_selection` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_settings`
--

INSERT INTO `payment_settings` (`id`, `default_currency`, `allow_all_currencies_for_classied`, `currency_converter`, `auto_update_exchange_rates`, `currency_converter_api`, `currency_converter_api_key`, `bank_transfer_enabled`, `bank_transfer_accounts`, `cash_on_delivery_enabled`, `cash_on_delivery_debt_limit`, `price_per_day`, `price_per_month`, `free_product_promotion`, `payout_paypal_enabled`, `payout_bitcoin_enabled`, `payout_iban_enabled`, `payout_swift_enabled`, `min_payout_paypal`, `min_payout_bitcoin`, `min_payout_iban`, `min_payout_swift`, `commission_rate`, `vat_status`, `wallet_status`, `wallet_deposit`, `wallet_min_deposit`, `pay_with_wallet_balance`, `additional_invoice_info`, `cart_location_selection`) VALUES
(1, 'USD', 1, 0, 0, NULL, NULL, 0, NULL, 0, 30.00, 0.10, 1.00, 0, 1, 0, 1, 1, 500.00, 500.00, 500.00, 500.00, 0, 1, 1, 1, 0.00, 1, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `payouts`
--

CREATE TABLE `payouts` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `payout_method` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT '0.00',
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `slug` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'physical',
  `listing_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'sell_on_site',
  `sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `price` decimal(12,2) DEFAULT '0.00',
  `price_discounted` decimal(12,2) DEFAULT '0.00',
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `discount_rate` smallint DEFAULT '0',
  `vat_rate` double DEFAULT '0',
  `user_id` int DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `is_promoted` tinyint(1) DEFAULT '0',
  `promote_start_date` timestamp NULL DEFAULT NULL,
  `promote_end_date` timestamp NULL DEFAULT NULL,
  `promote_plan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `promote_day` int DEFAULT NULL,
  `is_special_offer` tinyint(1) DEFAULT '0',
  `special_offer_date` timestamp NULL DEFAULT NULL,
  `visibility` tinyint(1) NOT NULL DEFAULT '1',
  `rating` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0',
  `pageviews` int DEFAULT '0',
  `demo_url` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `external_link` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `files_included` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `stock` int DEFAULT '1',
  `shipping_delivery_time_id` int DEFAULT NULL,
  `shipping_dimensions` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `chargeable_weight` decimal(10,3) DEFAULT NULL,
  `multiple_sale` tinyint(1) DEFAULT '1',
  `digital_file_download_link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country_id` int DEFAULT '0',
  `state_id` int DEFAULT '0',
  `city_id` int DEFAULT '0',
  `address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zip_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `brand_id` int DEFAULT '0',
  `is_sold` tinyint(1) DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  `is_draft` tinyint(1) DEFAULT '0',
  `is_edited` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `is_free_product` tinyint(1) DEFAULT '0',
  `is_rejected` tinyint(1) DEFAULT '0',
  `reject_reason` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_affiliate` tinyint(1) DEFAULT '0',
  `is_commission_set` tinyint(1) DEFAULT '0',
  `commission_rate` decimal(5,2) DEFAULT '0.00',
  `image_cache` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_details`
--

CREATE TABLE `product_details` (
  `id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `lang_id` tinyint DEFAULT NULL,
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `short_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_license_keys`
--

CREATE TABLE `product_license_keys` (
  `id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `license_key` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_options`
--

CREATE TABLE `product_options` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `option_name_translations` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `option_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `display_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `option_key` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_option_values`
--

CREATE TABLE `product_option_values` (
  `id` bigint NOT NULL,
  `option_id` int NOT NULL,
  `value_name_translations` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `color_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `primary_swatch_image_id` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gallery_image_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `display_order` int DEFAULT '0',
  `value_key` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_option_variants`
--

CREATE TABLE `product_option_variants` (
  `id` bigint NOT NULL,
  `product_id` int NOT NULL,
  `sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` decimal(12,2) DEFAULT '0.00',
  `price_discounted` decimal(12,2) DEFAULT '0.00',
  `quantity` int DEFAULT NULL,
  `weight` decimal(10,3) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `variant_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_option_variant_values`
--

CREATE TABLE `product_option_variant_values` (
  `id` bigint NOT NULL,
  `variant_id` bigint NOT NULL,
  `value_id` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_search_indexes`
--

CREATE TABLE `product_search_indexes` (
  `id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `lang_id` int DEFAULT NULL,
  `search_index` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_settings`
--

CREATE TABLE `product_settings` (
  `id` int NOT NULL,
  `marketplace_sku` tinyint(1) DEFAULT '1',
  `marketplace_variations` tinyint(1) DEFAULT '1',
  `marketplace_shipping` tinyint(1) DEFAULT '1',
  `marketplace_product_location` tinyint(1) DEFAULT '1',
  `classified_price` tinyint(1) DEFAULT '1',
  `classified_price_required` tinyint(1) DEFAULT '1',
  `classified_product_location` tinyint(1) DEFAULT '1',
  `classified_external_link` tinyint(1) DEFAULT '1',
  `physical_demo_url` tinyint(1) DEFAULT '0',
  `physical_video_preview` tinyint(1) DEFAULT '1',
  `physical_audio_preview` tinyint(1) DEFAULT '1',
  `digital_demo_url` tinyint(1) DEFAULT '1',
  `digital_video_preview` tinyint(1) DEFAULT '1',
  `digital_audio_preview` tinyint(1) DEFAULT '1',
  `digital_external_link` tinyint(1) DEFAULT '1',
  `digital_allowed_file_extensions` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'zip',
  `is_product_image_required` tinyint(1) DEFAULT '1',
  `image_file_format` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'JPG',
  `product_image_limit` smallint DEFAULT '20',
  `brand_status` tinyint(1) DEFAULT '0',
  `is_brand_optional` tinyint(1) DEFAULT '1',
  `brand_where_to_display` tinyint DEFAULT '2',
  `max_file_size_image` bigint DEFAULT '10485760',
  `max_file_size_video` bigint DEFAULT '31457280',
  `max_file_size_audio` bigint DEFAULT '10485760',
  `sitemap_frequency` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'monthly',
  `sitemap_last_modification` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'server_response',
  `sitemap_priority` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'automatically',
  `pagination_per_page` smallint DEFAULT '60',
  `sort_by_featured_products` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_settings`
--

INSERT INTO `product_settings` (`id`, `marketplace_sku`, `marketplace_variations`, `marketplace_shipping`, `marketplace_product_location`, `classified_price`, `classified_price_required`, `classified_product_location`, `classified_external_link`, `physical_demo_url`, `physical_video_preview`, `physical_audio_preview`, `digital_demo_url`, `digital_video_preview`, `digital_audio_preview`, `digital_external_link`, `digital_allowed_file_extensions`, `is_product_image_required`, `image_file_format`, `product_image_limit`, `brand_status`, `is_brand_optional`, `brand_where_to_display`, `max_file_size_image`, `max_file_size_video`, `max_file_size_audio`, `sitemap_frequency`, `sitemap_last_modification`, `sitemap_priority`, `pagination_per_page`, `sort_by_featured_products`) VALUES
(1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'zip', 1, 'JPG', 20, 0, 1, 2, 10485760, 31457280, 10485760, 'daily', 'server_response', 'automatically', 60, 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_tags`
--

CREATE TABLE `product_tags` (
  `id` int NOT NULL,
  `tag_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promoted_transactions`
--

CREATE TABLE `promoted_transactions` (
  `id` int NOT NULL,
  `payment_method` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_amount` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `purchased_plan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `day_count` int DEFAULT NULL,
  `global_taxes_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `checkout_token` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quote_requests`
--

CREATE TABLE `quote_requests` (
  `id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `product_title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_quantity` mediumint DEFAULT '1',
  `seller_id` int DEFAULT NULL,
  `buyer_id` int DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `price_offered` decimal(12,2) DEFAULT '0.00',
  `price_currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_buyer_deleted` tinyint(1) DEFAULT '0',
  `is_seller_deleted` tinyint(1) DEFAULT '0',
  `variation_option_ids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `variant_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `extra_options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `extra_options_hash` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `product_options_snapshot` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `product_options_summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `product_image_id` int DEFAULT NULL,
  `product_image_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refund_requests`
--

CREATE TABLE `refund_requests` (
  `id` int NOT NULL,
  `buyer_id` int DEFAULT NULL,
  `seller_id` int DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `order_number` bigint DEFAULT NULL,
  `order_product_id` int DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `is_completed` tinyint(1) DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refund_requests_messages`
--

CREATE TABLE `refund_requests_messages` (
  `id` int NOT NULL,
  `request_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `is_buyer` tinyint(1) NOT NULL DEFAULT '1',
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `review` varchar(10000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles_permissions`
--

CREATE TABLE `roles_permissions` (
  `id` int NOT NULL,
  `role_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `permissions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_super_admin` tinyint(1) DEFAULT '0',
  `is_default` tinyint(1) DEFAULT '0',
  `is_admin` tinyint(1) DEFAULT '0',
  `is_vendor` tinyint(1) DEFAULT '0',
  `is_member` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles_permissions`
--

INSERT INTO `roles_permissions` (`id`, `role_name`, `permissions`, `is_super_admin`, `is_default`, `is_admin`, `is_vendor`, `is_member`) VALUES
(1, 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:11:\"Super Admin\";}}', 'all', 1, 1, 1, 0, 0),
(2, 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:6:\"Vendor\";}}', '2', 0, 1, 0, 1, 0),
(3, 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:6:\"Member\";}}', '', 0, 1, 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `id` int NOT NULL,
  `route_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `route` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`id`, `route_key`, `route`) VALUES
(1, 'add_coupon', 'add-coupon'),
(2, 'add_product', 'add-product'),
(3, 'add_shipping_zone', 'add-shipping-zone'),
(4, 'admin', 'admin'),
(5, 'affiliate', 'affiliate'),
(6, 'affiliate_links', 'affiliate-links'),
(7, 'affiliate_program', 'affiliate-program'),
(8, 'blog', 'blog'),
(9, 'bulk_product_upload', 'bulk-product-upload'),
(10, 'cart', 'cart'),
(11, 'cash_on_delivery', 'cash-on-delivery'),
(12, 'category', 'category'),
(13, 'change_password', 'change-password'),
(14, 'checkout', 'checkout'),
(15, 'comments', 'comments'),
(16, 'contact', 'contact'),
(17, 'coupons', 'coupons'),
(18, 'coupon_products', 'coupon-products'),
(19, 'dashboard', 'dashboard'),
(20, 'delete_account', 'delete-account'),
(21, 'downloads', 'downloads'),
(22, 'earnings', 'earnings'),
(23, 'edit_coupon', 'edit-coupon'),
(24, 'edit_product', 'edit-product'),
(25, 'edit_profile', 'edit-profile'),
(26, 'edit_shipping_zone', 'edit-shipping-zone'),
(27, 'featured_products', 'featured-products'),
(28, 'followers', 'followers'),
(29, 'following', 'following'),
(30, 'forgot_password', 'forgot-password'),
(31, 'help_center', 'help-center'),
(32, 'latest_products', 'latest-products'),
(33, 'location', 'location'),
(34, 'members', 'members'),
(35, 'messages', 'messages'),
(36, 'my_coupons', 'my-coupons'),
(37, 'my_reviews', 'my-reviews'),
(38, 'orders', 'orders'),
(39, 'order_completed', 'order-completed'),
(40, 'order_details', 'order-details'),
(41, 'payment', 'payment'),
(42, 'payments', 'payments'),
(43, 'payment_method', 'payment-method'),
(44, 'product', 'product'),
(45, 'products', 'products'),
(46, 'product_details', 'product-details'),
(47, 'profile', 'profile'),
(48, 'quote_requests', 'quote-requests'),
(49, 'refund_requests', 'refund-requests'),
(50, 'register', 'register'),
(51, 'register_success', 'register-success'),
(52, 'reset_password', 'reset-password'),
(53, 'reviews', 'reviews'),
(54, 'rss_feeds', 'rss-feeds'),
(55, 'sale', 'sale'),
(56, 'sales', 'sales'),
(57, 'search', 'search'),
(58, 'select_membership_plan', 'select-membership-plan'),
(59, 'seller', 'seller'),
(60, 'service_payment_completed', 'service-payment-completed'),
(61, 'settings', 'settings'),
(62, 'shipping', 'shipping'),
(63, 'shipping_address', 'shipping-address'),
(64, 'shipping_settings', 'shipping-settings'),
(65, 'shops', 'shops'),
(66, 'shop_policies', 'shop-policies'),
(67, 'shop_settings', 'shop-settings'),
(68, 'social_media', 'social-media'),
(69, 'start_selling', 'start-selling'),
(70, 'submit_request', 'submit-request'),
(71, 'tag', 'tag'),
(72, 'terms_conditions', 'terms-conditions'),
(73, 'ticket', 'ticket'),
(74, 'tickets', 'tickets'),
(75, 'wallet', 'wallet'),
(76, 'wishlist', 'wishlist');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `lang_id` int DEFAULT '1',
  `site_font` smallint DEFAULT '19',
  `dashboard_font` smallint DEFAULT '22',
  `site_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `homepage_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Home',
  `site_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keywords` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `social_media_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `about_footer` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `contact_address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `copyright` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cookies_warning` tinyint(1) DEFAULT '0',
  `cookies_warning_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `affiliate_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `affiliate_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `affiliate_faq` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `affiliate_works` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `bulk_upload_documentation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `lang_id`, `site_font`, `dashboard_font`, `site_title`, `homepage_title`, `site_description`, `keywords`, `social_media_data`, `about_footer`, `contact_text`, `contact_address`, `contact_email`, `contact_phone`, `copyright`, `cookies_warning`, `cookies_warning_text`, `affiliate_description`, `affiliate_content`, `affiliate_faq`, `affiliate_works`, `bulk_upload_documentation`, `created_at`) VALUES
(1, 1, 21, 24, 'Modesy - Marketplace - Classified Ads Script', 'Home', 'Modesy Index Page', 'index, home, modesy', NULL, NULL, NULL, NULL, NULL, NULL, 'Copyright 2025 Modesy - All Rights Reserved.', 1, '<p>This site uses cookies. By continuing to browse the site, you are agreeing to our use of cookies.</p>', 'a:2:{s:5:\"title\";s:53:\"Boost Your Earnings with the Modesy Affiliate Program\";s:11:\"description\";s:196:\"Are you a content creator, blogger, influencer, or simply someone with a strong online presence? If so, Modesy has an exciting opportunity for you to turn your online influence into real earnings.\";}', 'a:2:{s:5:\"title\";s:38:\"Why Join the Modesy Affiliate Program?\";s:7:\"content\";s:1745:\"<p>Modesy, a leading e-commerce platform known for its diverse range of products and exceptional customer service, is thrilled to introduce its Affiliate Program. This program offers you a chance to earn lucrative commissions by promoting Modesy\'s products. Here’s everything you need to know about why the Modesy Affiliate Program is perfect for you.<br><br><strong>1. Attractive Commission Rates</strong><br>Modesy offers competitive commission rates that ensure you are rewarded generously for your efforts. Every time someone makes a purchase through your referral link, you earn a commission. The more you promote, the more you earn.</p>\r\n<p><strong>2. Wide Range of Products</strong><br>With Modesy’s extensive catalog, you have endless opportunities to promote products that resonate with your audience. Whether your niche is tech gadgets, fashion, beauty products, or home decor, Modesy has something for everyone.</p>\r\n<p><strong>3. Easy-to-Use Tools</strong><br>The Modesy Affiliate Program provides you with a suite of tools to make your promotional efforts seamless. From custom referral links to detailed performance reports, you’ll have everything you need to track your success and optimize your strategies.</p>\r\n<p><strong>4. Reliable Support</strong><br>Modesy values its affiliates and offers dedicated support to help you succeed. Whether you have questions about the program or need tips on how to maximize your earnings, the Modesy support team is always ready to assist you.</p>\r\n<p><strong>5. Timely Payments</strong><br>Modesy ensures that your hard-earned commissions are paid out on time. With a straightforward payout process, you can focus on what you do best – promoting great products and earning money.</p>\";}', 'a:8:{i:0;a:3:{s:1:\"o\";s:1:\"1\";s:1:\"q\";s:36:\"How do I join the Affiliate program?\";s:1:\"a\";s:110:\"Simply click the \"Join Now\" button and fill out the registration form. Once approved, you can start promoting!\";}i:1;a:3:{s:1:\"o\";s:1:\"2\";s:1:\"q\";s:45:\"Who can participate in the Affiliate Program?\";s:1:\"a\";s:215:\"Anyone with an online presence, including bloggers, social media influencers, website owners, and content creators, can join the affiliate program. As long as you can promote our products, you’re welcome to apply!\";}i:2;a:3:{s:1:\"o\";s:1:\"3\";s:1:\"q\";s:40:\"Where can I generate my Affiliate links?\";s:1:\"a\";s:222:\"You can generate your affiliate links directly from any product detail page on our website. Once logged in, visit the product page you want to promote, and you’ll find an option to create your affiliate link right there.\";}i:3;a:3:{s:1:\"o\";s:1:\"4\";s:1:\"q\";s:28:\"What products can I promote?\";s:1:\"a\";s:162:\"You can promote any product from our store that is included in the affiliate program and earn commission on any qualifying sales made through your affiliate link.\";}i:4;a:3:{s:1:\"o\";s:1:\"5\";s:1:\"q\";s:46:\"How long is the validity of an Affiliate link?\";s:1:\"a\";s:211:\"An affiliate link is valid for 30 days from the moment a person clicks on it and opens the product page. If the product is purchased during this period, the affiliate commission will be applied for that product.\";}i:5;a:3:{s:1:\"o\";s:1:\"6\";s:1:\"q\";s:20:\"How much can I earn?\";s:1:\"a\";s:120:\"There is no limit to how much you can earn. Your earnings depend on the sales you generate through your affiliate links.\";}i:6;a:3:{s:1:\"o\";s:1:\"7\";s:1:\"q\";s:37:\"How do I track my Affiliate earnings?\";s:1:\"a\";s:96:\"You can track your affiliate program earnings in the \"Referral Earnings\" section of your wallet.\";}i:7;a:3:{s:1:\"o\";s:1:\"8\";s:1:\"q\";s:35:\"How do I get my Affiliate earnings?\";s:1:\"a\";s:188:\"Once your earnings exceed the minimum payout limit, you can request a payment from the \"Payouts\" section of your wallet. Simply submit a payout request, and your payment will be processed.\";}}', 'a:3:{i:0;a:2:{s:5:\"title\";s:23:\"Sign up for the program\";s:11:\"description\";s:77:\"Join the Modesy affiliate program by completing a simple registration process\";}i:1;a:2:{s:5:\"title\";s:34:\"Create and share your referral URL\";s:11:\"description\";s:77:\"Generate a referral URL and share it on your website, email, or social media.\";}i:2;a:2:{s:5:\"title\";s:15:\"Earn commission\";s:11:\"description\";s:64:\"Earn commissions on every sale made through your affiliate links\";}}', '<p>With the <strong>bulk product upload</strong> feature, you can easily upload or update multiple products at once using a CSV file. This saves time and ensures consistency when managing a large number of products.</p>\n<p><br><strong>How It Works</strong></p>\n<p>There are two main ways to use the bulk upload feature:</p>\n<p><br><strong>1. Add New Products</strong></p>\n<ol>\n<li>Download the <strong>CSV template</strong> from this section.</li>\n<li>Add your products into the CSV file by filling in the required and optional columns.</li>\n<li>Upload the completed CSV file back into the system.</li>\n</ol>\n<p><em>Important:</em> Please pay attention to the data type of each column (e.g., numbers, text, dates). You can see detailed explanations of all required and optional columns in the table below.</p>\n<p><strong>2. Edit Existing Products</strong></p>\n<ol>\n<li>Add an <strong>\"id\"</strong> column to the CSV template. (You can find the product ID numbers on the <strong>Products</strong> page.)</li>\n<li>Include only the columns you want to update (for example, price, stock, etc.).</li>\n<li>Upload the updated CSV file to apply the changes.</li>\n</ol>\n<p>For example, if you want to update the stock and prices of your products, your CSV file should look like this:</p>\n<pre style=\"background: #f7f7f7; padding: 10px; border-radius: 6px; overflow-x: auto;\">\"id\",\"price\",\"price_discounted\",\"stock\"\n\"1\",\"30\",\"20\",\"1000\"\n\"5\",\"40\",\"40\",\"500\"\n</pre>\n<p style=\"color: #ba372a;\">* To update product prices, you must include both the <strong>\"price\"</strong> and <strong>\"price_discounted\"</strong> columns in your CSV file.</p>\n<hr>\n<p><strong>Multi-language Support</strong></p>\n<p>The CSV upload feature also supports <strong>multiple languages</strong>. This allows you to add product information in more than one language, making your store multilingual.</p>\n<p><strong>How to Add Multiple Languages</strong></p>\n<ol>\n<li>Each language in your system has a <strong>Language ID</strong> (for example, English = 1, German = 2). You can find the language ID numbers on the <strong>Languages</strong> page.</li>\n<li>In your CSV file, add extra columns for each language, following this format:\n<ul>\n<li>For the main language: <code>title</code>, <code>short_description</code>, <code>description</code>, <code>tags</code></li>\n<li>For a second language with ID 2: <code>title_lang2</code>, <code>short_description_lang2</code>, <code>description_lang2</code>, <code>tags_lang2</code></li>\n</ul>\n</li>\n<li>When you upload your CSV file, the system will automatically detect and save the translations.</li>\n</ol>\n<p><strong>Example</strong></p>\n<pre style=\"background: #f7f7f7; padding: 10px; border-radius: 6px; overflow-x: auto;\">\"id\",\"title\",\"short_description\",\"description\",\"tags\",....,\"title_lang2\",\"short_description_lang2\",\"description_lang2\",\"tags_lang2\"\n\"1\",\"Red Dress\",\"Beautiful red dress\",\"A lovely red dress for summer\",\"dress,red,fashion\",....,\"Rotes Kleid\",\"Schönes rotes Kleid\",\"Ein hübsches rotes Kleid für den Sommer\",\"kleid,rot,mode\"\n</pre>\n<p>In this example: - The product is added in English (main language). - The same product details are also added in German (Language ID: 2). - Both language versions will be available on your site.</p>\n<p><br><br></p>\n<p><span style=\"font-size: 12pt;\"><strong>CSV Columns</strong></span></p>\n<table style=\"width: 100%;\" class=\"table table-bordered\">\n<tbody>\n<tr>\n<th>Column</th>\n<th>Description</th>\n</tr>\n<tr>\n<td style=\"width: 180px;\">title</td>\n<td>Data Type: Text <br><strong>Required</strong><br>Example: Modern grey couch and pillows</td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">slug</td>\n<td>Data Type: Text <br><strong>Optional</strong> <small>(If you leave it empty, it will be generated automatically.)</small> <br>Example: modern-grey-couch-and-pillows</td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">sku</td>\n<td>Data Type: Text <br><strong>Optional</strong><br>Example: MD-GR-6898</td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">category_id</td>\n<td>Data Type: Number <br><strong>Required</strong><br>Example: 1</td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">brand_id</td>\n<td>Data Type: Number <br><strong>Optional</strong><br>Example: 1</td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">price</td>\n<td>Data Type: Decimal/Number <br><strong>Required</strong><br>Example: 50<br>Example: 45.90<br>Example: 3456.25</td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">price_discounted</td>\n<td>Data Type: Decimal/Number <br><strong>Optional</strong><br>Example: 40<br>Example: 35.90<br>Example: 2456.25</td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">vat_rate</td>\n<td>Data Type: Number (Percentage) <br><strong>Optional</strong><br>Example: 8</td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">stock</td>\n<td>Data Type: Number <br><strong>Required</strong><br>Example: 100</td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">weight</td>\n<td>Data Type: Decimal <br><strong>Optional</strong><br>Example: 1.2</td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">short_description</td>\n<td>Data Type: Text <br><strong>Optional</strong><br>Example: It is a nice and comfortable couch</td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">description</td>\n<td>Data Type: Text <br><strong>Optional</strong><br>Example: It is a nice and comfortable couch...</td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">tags</td>\n<td>Data Type: Text <br><strong>Optional</strong><br>Example: nice, comfortable, couch</td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">image_url</td>\n<td>Data Type: Text <br><strong>Optional</strong><br>Example:<br>https://upload.wikimedia.org/wikipedia/commons/7/70/Labrador-sea-paamiut.jpg<br><br>Example:<br>https://upload.wikimedia.org/wikipedia/commons/7/70/Labrador-sea-paamiut.jpg,<br>https://upload.wikimedia.org/wikipedia/commons/thumb/4/42/Shaqi_jrvej.jpg/1600px-Shaqi_jrvej.jpg<br><br><span style=\"color: rgb(186, 55, 42);\">*You can add multiple image links by placing commas between them.</span></td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">external_link</td>\n<td>Data Type: Text <br><strong>Optional</strong><br>Example: https://domain.com/product_url</td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">updated_at</td>\n<td>Data Type: Timestamp <br><strong>Optional</strong><br>Example: 2024-06-30 10:27:00 <br><br><span style=\"color: rgb(186, 55, 42);\">*If you leave it blank, the system will not assign an update date.</span></td>\n</tr>\n<tr>\n<td style=\"width: 180px;\">created_at</td>\n<td>Data Type: Timestamp <br><strong>Optional</strong><br>Example: 2024-06-30 10:27:00 <br><br><span style=\"color: rgb(186, 55, 42);\">*If you leave it blank, the system will automatically assign the current date.</span></td>\n</tr>\n</tbody>\n</table>', '2025-08-24 07:40:30');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_addresses`
--

CREATE TABLE `shipping_addresses` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `state_id` int DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zip_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'shipping',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_delivery_times`
--

CREATE TABLE `shipping_delivery_times` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `option_array` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_zones`
--

CREATE TABLE `shipping_zones` (
  `id` int NOT NULL,
  `name_array` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `estimated_delivery` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_zone_locations`
--

CREATE TABLE `shipping_zone_locations` (
  `id` int NOT NULL,
  `zone_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `continent_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country_id` int DEFAULT '0',
  `state_id` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_zone_methods`
--

CREATE TABLE `shipping_zone_methods` (
  `id` int NOT NULL,
  `name_array` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `zone_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `method_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cost_calculation_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shipping_flat_cost` decimal(12,2) DEFAULT NULL,
  `flat_rate_costs` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `local_pickup_cost` decimal(12,2) DEFAULT NULL,
  `free_shipping_min_amount` decimal(12,2) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `slider`
--

CREATE TABLE `slider` (
  `id` int NOT NULL,
  `lang_id` tinyint DEFAULT '1',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `item_order` smallint DEFAULT '1',
  `button_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `animation_title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'none',
  `animation_description` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'none',
  `animation_button` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'none',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_mobile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `text_color` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '#ffffff',
  `button_color` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '#222222',
  `button_text_color` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '#ffffff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_subtickets`
--

CREATE TABLE `support_subtickets` (
  `id` int NOT NULL,
  `ticket_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `attachments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_support_reply` tinyint(1) DEFAULT '0',
  `storage` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subject` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_guest` tinyint(1) DEFAULT '0',
  `status` smallint DEFAULT '1',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int NOT NULL,
  `tag` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lang_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

CREATE TABLE `taxes` (
  `id` int NOT NULL,
  `name_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `tax_rate` double NOT NULL,
  `is_all_countries` tinyint(1) DEFAULT '0',
  `country_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `state_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `product_sales` tinyint(1) DEFAULT '1',
  `service_payments` tinyint(1) DEFAULT '1',
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `user_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_amount` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `checkout_token` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'name@domain.com',
  `email_status` tinyint(1) DEFAULT '0',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `token` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role_id` smallint DEFAULT '3',
  `balance` decimal(12,2) DEFAULT '0.00',
  `number_of_sales` int DEFAULT '0',
  `user_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'registered',
  `facebook_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `google_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vkontakte_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cover_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cover_image_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'full_width',
  `banned` tinyint(1) DEFAULT '0',
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `about_me` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country_id` int DEFAULT NULL,
  `state_id` int DEFAULT NULL,
  `city_id` int DEFAULT NULL,
  `address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zip_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `show_email` tinyint(1) DEFAULT '0',
  `show_phone` tinyint(1) DEFAULT '0',
  `show_location` tinyint(1) DEFAULT '0',
  `social_media_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `last_seen` timestamp NULL DEFAULT NULL,
  `show_rss_feeds` tinyint(1) DEFAULT '0',
  `send_email_new_message` tinyint(1) DEFAULT '0',
  `is_active_shop_request` tinyint(1) DEFAULT '0',
  `shop_request_reject_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `shop_request_date` timestamp NULL DEFAULT NULL,
  `vendor_documents` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_membership_plan_expired` tinyint(1) DEFAULT '0',
  `is_used_free_plan` tinyint(1) DEFAULT '0',
  `cash_on_delivery` tinyint(1) DEFAULT '0',
  `is_fixed_vat` tinyint(1) DEFAULT '0',
  `fixed_vat_rate` double DEFAULT '0',
  `vat_rates_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `vat_rates_data_state` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_affiliate` tinyint(1) DEFAULT '0',
  `vendor_affiliate_status` tinyint(1) DEFAULT '0',
  `affiliate_commission_rate` double DEFAULT '0',
  `affiliate_discount_rate` double DEFAULT '0',
  `tax_registration_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vacation_mode` tinyint(1) DEFAULT '0',
  `vacation_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `commission_debt` bigint DEFAULT NULL,
  `account_delete_req` tinyint(1) DEFAULT '0',
  `account_delete_req_date` timestamp NULL DEFAULT NULL,
  `storage_avatar` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `storage_cover` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'local',
  `is_commission_set` tinyint(1) DEFAULT '0',
  `commission_rate` decimal(5,2) DEFAULT '0.00',
  `payout_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_membership_plans`
--

CREATE TABLE `users_membership_plans` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `plan_id` int DEFAULT NULL,
  `plan_title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `number_of_ads` int DEFAULT NULL,
  `number_of_days` int DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'USD',
  `is_free` tinyint(1) DEFAULT '0',
  `is_unlimited_number_of_ads` tinyint(1) DEFAULT '0',
  `is_unlimited_time` tinyint(1) DEFAULT '0',
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `plan_status` tinyint(1) DEFAULT '0',
  `plan_start_date` timestamp NULL DEFAULT NULL,
  `plan_end_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_login_activities`
--

CREATE TABLE `user_login_activities` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `ip_address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_deposits`
--

CREATE TABLE `wallet_deposits` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deposit_amount` decimal(12,2) DEFAULT '0.00',
  `deposit_amount_base` decimal(12,2) DEFAULT NULL,
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'USD',
  `currency_base` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_status` tinyint(1) DEFAULT '0',
  `checkout_token` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_expenses`
--

CREATE TABLE `wallet_expenses` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `payment_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expense_item_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expense_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expense_amount` decimal(12,2) DEFAULT '0.00',
  `expense_detail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `currency` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'USD',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abuse_reports`
--
ALTER TABLE `abuse_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ad_spaces`
--
ALTER TABLE `ad_spaces`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `affiliate_earnings`
--
ALTER TABLE `affiliate_earnings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `affiliate_links`
--
ALTER TABLE `affiliate_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_link_short` (`link_short`);

--
-- Indexes for table `bank_transfers`
--
ALTER TABLE `bank_transfers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lang_id` (`lang_id`);

--
-- Indexes for table `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_id` (`post_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `blog_images`
--
ALTER TABLE `blog_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lang_id` (`lang_id`),
  ADD KEY `idx_category_id` (`category_id`);

--
-- Indexes for table `blog_tags`
--
ALTER TABLE `blog_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_post_id` (`post_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_show_on_slider` (`show_on_slider`);

--
-- Indexes for table `brand_category`
--
ALTER TABLE `brand_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_brand_category` (`brand_id`,`category_id`);

--
-- Indexes for table `brand_lang`
--
ALTER TABLE `brand_lang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_brand_lang` (`brand_id`,`lang_id`,`name`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_session_id` (`session_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cart_id` (`cart_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_is_featured` (`is_featured`),
  ADD KEY `idx_show_products_on_index` (`show_products_on_index`),
  ADD KEY `idx_category_order` (`category_order`),
  ADD KEY `idx_featured_order` (`featured_order`),
  ADD KEY `idx_show_on_main_menu` (`show_on_main_menu`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `category_lang`
--
ALTER TABLE `category_lang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_lang_id` (`lang_id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_lang_category` (`lang_id`,`category_id`);

--
-- Indexes for table `category_paths`
--
ALTER TABLE `category_paths`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ancestor_id` (`ancestor_id`),
  ADD KEY `idx_descendant_id` (`descendant_id`),
  ADD KEY `idx_depth` (`depth`),
  ADD KEY `idx_descendant_ancestor` (`descendant_id`,`ancestor_id`);

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sender_id` (`sender_id`),
  ADD KEY `idx_receiver_id` (`receiver_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversation_id` (`chat_id`),
  ADD KEY `idx_sender_id` (`sender_id`),
  ADD KEY `idx_receiver_id` (`receiver_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_deleted_user_id` (`deleted_user_id`);

--
-- Indexes for table `checkouts`
--
ALTER TABLE `checkouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `checkout_items`
--
ALTER TABLE `checkout_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_checkout_id` (`checkout_id`);

--
-- Indexes for table `ci_sessions`
--
ALTER TABLE `ci_sessions`
  ADD KEY `ci_sessions_timestamp` (`timestamp`),
  ADD KEY `ci_sessions_id` (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_product_comments` (`product_id`,`parent_id`,`status`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons_used`
--
ALTER TABLE `coupons_used`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupon_products`
--
ALTER TABLE `coupon_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_coupon_id` (`coupon_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `custom_fields`
--
ALTER TABLE `custom_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_where_to_display` (`where_to_display`),
  ADD KEY `idx_field_order` (`field_order`),
  ADD KEY `idx_is_product_filter` (`is_product_filter`);

--
-- Indexes for table `custom_fields_category`
--
ALTER TABLE `custom_fields_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_field_id` (`field_id`);

--
-- Indexes for table `custom_fields_options`
--
ALTER TABLE `custom_fields_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_field_id` (`field_id`),
  ADD KEY `idx_option_key` (`option_key`);

--
-- Indexes for table `custom_fields_product`
--
ALTER TABLE `custom_fields_product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_field_id` (`field_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_selected_option_id` (`selected_option_id`),
  ADD KEY `idx_product_filter_key` (`product_filter_key`);

--
-- Indexes for table `custom_field_lang`
--
ALTER TABLE `custom_field_lang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_field_lang` (`field_id`,`lang_id`);

--
-- Indexes for table `custom_field_option_lang`
--
ALTER TABLE `custom_field_option_lang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_option_lang` (`option_id`,`lang_id`);

--
-- Indexes for table `digital_files`
--
ALTER TABLE `digital_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `digital_sales`
--
ALTER TABLE `digital_sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_seller_id` (`seller_id`),
  ADD KEY `idx_buyer_id` (`buyer_id`);

--
-- Indexes for table `earnings`
--
ALTER TABLE `earnings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `followers`
--
ALTER TABLE `followers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_following_id` (`following_id`),
  ADD KEY `idx_follower_id` (`follower_id`);

--
-- Indexes for table `fonts`
--
ALTER TABLE `fonts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `general_settings`
--
ALTER TABLE `general_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `homepage_banners`
--
ALTER TABLE `homepage_banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_is_main` (`is_main`),
  ADD KEY `idx_is_option_image` (`is_option_image`);

--
-- Indexes for table `images_file_manager`
--
ALTER TABLE `images_file_manager`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indexes for table `knowledge_base`
--
ALTER TABLE `knowledge_base`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `knowledge_base_categories`
--
ALTER TABLE `knowledge_base_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `language_translations`
--
ALTER TABLE `language_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lang_id` (`lang_id`);

--
-- Indexes for table `location_cities`
--
ALTER TABLE `location_cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_country_id` (`country_id`),
  ADD KEY `idx_state_id` (`state_id`);

--
-- Indexes for table `location_countries`
--
ALTER TABLE `location_countries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `location_states`
--
ALTER TABLE `location_states`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_country_id` (`country_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `membership_plans`
--
ALTER TABLE `membership_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `membership_transactions`
--
ALTER TABLE `membership_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_membership_checkout_token` (`checkout_token`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_orders_checkout_token` (`checkout_token`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_buyer_id` (`buyer_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_seller_id` (`seller_id`),
  ADD KEY `idx_buyer_id` (`buyer_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_image_id` (`image_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages_vendor`
--
ALTER TABLE `pages_vendor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_settings`
--
ALTER TABLE `payment_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payouts`
--
ALTER TABLE `payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_promoted` (`is_promoted`),
  ADD KEY `idx_visibility` (`visibility`),
  ADD KEY `idx_is_deleted` (`is_deleted`),
  ADD KEY `idx_is_draft` (`is_draft`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_discount_rate` (`discount_rate`),
  ADD KEY `idx_is_special_offer` (`is_special_offer`),
  ADD KEY `idx_is_sold` (`is_sold`),
  ADD KEY `idx_brand_id` (`brand_id`),
  ADD KEY `idx_price_discounted` (`price_discounted`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_country_id` (`country_id`),
  ADD KEY `idx_state_id` (`state_id`),
  ADD KEY `idx_city_id` (`city_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_sku` (`sku`),
  ADD KEY `idx_is_edited` (`is_edited`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_active_products` (`is_active`,`is_promoted`,`created_at`,`user_id`),
  ADD KEY `idx_category_products` (`is_active`,`category_id`,`is_promoted`,`created_at`,`user_id`),
  ADD KEY `idx_active_user_products` (`user_id`,`is_active`),
  ADD KEY `idx_products_admin` (`is_deleted`,`is_draft`,`status`,`user_id`,`visibility`);

--
-- Indexes for table `product_details`
--
ALTER TABLE `product_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_lang_id` (`lang_id`),
  ADD KEY `idx_product_lang` (`product_id`,`lang_id`);

--
-- Indexes for table `product_license_keys`
--
ALTER TABLE `product_license_keys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_is_used` (`is_used`);

--
-- Indexes for table `product_options`
--
ALTER TABLE `product_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `product_option_values`
--
ALTER TABLE `product_option_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_option_id` (`option_id`);

--
-- Indexes for table `product_option_variants`
--
ALTER TABLE `product_option_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_sku` (`sku`),
  ADD KEY `idx_is_default` (`is_default`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_variant_hash` (`variant_hash`);

--
-- Indexes for table `product_option_variant_values`
--
ALTER TABLE `product_option_variant_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_variant_value` (`variant_id`,`value_id`);

--
-- Indexes for table `product_search_indexes`
--
ALTER TABLE `product_search_indexes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_lang_id` (`lang_id`);
ALTER TABLE `product_search_indexes` ADD FULLTEXT KEY `search_index` (`search_index`);

--
-- Indexes for table `product_settings`
--
ALTER TABLE `product_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_tags`
--
ALTER TABLE `product_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tag_id` (`tag_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `promoted_transactions`
--
ALTER TABLE `promoted_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_promoted_checkout_token` (`checkout_token`);

--
-- Indexes for table `quote_requests`
--
ALTER TABLE `quote_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_seller_id` (`seller_id`),
  ADD KEY `idx_buyer_id` (`buyer_id`),
  ADD KEY `idx_is_buyer_deleted` (`is_buyer_deleted`),
  ADD KEY `idx_is_seller_deleted` (`is_seller_deleted`);

--
-- Indexes for table `refund_requests`
--
ALTER TABLE `refund_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_buyer_id` (`buyer_id`),
  ADD KEY `idx_seller_id` (`seller_id`);

--
-- Indexes for table `refund_requests_messages`
--
ALTER TABLE `refund_requests_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `roles_permissions`
--
ALTER TABLE `roles_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `shipping_delivery_times`
--
ALTER TABLE `shipping_delivery_times`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `shipping_zones`
--
ALTER TABLE `shipping_zones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `shipping_zone_locations`
--
ALTER TABLE `shipping_zone_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_zone_id` (`zone_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `shipping_zone_methods`
--
ALTER TABLE `shipping_zone_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_zone_id` (`zone_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `slider`
--
ALTER TABLE `slider`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_subtickets`
--
ALTER TABLE `support_subtickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ticket_id` (`ticket_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_support_reply` (`is_support_reply`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tag` (`tag`),
  ADD KEY `idx_lang_id` (`lang_id`);

--
-- Indexes for table `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_id` (`payment_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_checkout_token` (`checkout_token`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_banned` (`banned`),
  ADD KEY `idx_country_id` (`country_id`),
  ADD KEY `idx_state_id` (`state_id`),
  ADD KEY `idx_city_id` (`city_id`),
  ADD KEY `idx_vacation_mode` (`vacation_mode`),
  ADD KEY `idx_is_membership_plan_expired` (`is_membership_plan_expired`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_active_user` (`banned`,`vacation_mode`,`is_membership_plan_expired`);

--
-- Indexes for table `users_membership_plans`
--
ALTER TABLE `users_membership_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `user_login_activities`
--
ALTER TABLE `user_login_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `wallet_deposits`
--
ALTER TABLE `wallet_deposits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_deposits_checkout_token` (`checkout_token`);

--
-- Indexes for table `wallet_expenses`
--
ALTER TABLE `wallet_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_product_user` (`product_id`,`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abuse_reports`
--
ALTER TABLE `abuse_reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ad_spaces`
--
ALTER TABLE `ad_spaces`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `affiliate_earnings`
--
ALTER TABLE `affiliate_earnings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `affiliate_links`
--
ALTER TABLE `affiliate_links`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_transfers`
--
ALTER TABLE `bank_transfers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_comments`
--
ALTER TABLE `blog_comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_images`
--
ALTER TABLE `blog_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_tags`
--
ALTER TABLE `blog_tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brand_category`
--
ALTER TABLE `brand_category`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brand_lang`
--
ALTER TABLE `brand_lang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category_lang`
--
ALTER TABLE `category_lang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category_paths`
--
ALTER TABLE `category_paths`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checkouts`
--
ALTER TABLE `checkouts`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checkout_items`
--
ALTER TABLE `checkout_items`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons_used`
--
ALTER TABLE `coupons_used`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupon_products`
--
ALTER TABLE `coupon_products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_fields`
--
ALTER TABLE `custom_fields`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_fields_category`
--
ALTER TABLE `custom_fields_category`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_fields_options`
--
ALTER TABLE `custom_fields_options`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_fields_product`
--
ALTER TABLE `custom_fields_product`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_field_lang`
--
ALTER TABLE `custom_field_lang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_field_option_lang`
--
ALTER TABLE `custom_field_option_lang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `digital_files`
--
ALTER TABLE `digital_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `digital_sales`
--
ALTER TABLE `digital_sales`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `earnings`
--
ALTER TABLE `earnings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `followers`
--
ALTER TABLE `followers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fonts`
--
ALTER TABLE `fonts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `general_settings`
--
ALTER TABLE `general_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `homepage_banners`
--
ALTER TABLE `homepage_banners`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `images_file_manager`
--
ALTER TABLE `images_file_manager`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `knowledge_base`
--
ALTER TABLE `knowledge_base`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `knowledge_base_categories`
--
ALTER TABLE `knowledge_base_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `language_translations`
--
ALTER TABLE `language_translations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1586;

--
-- AUTO_INCREMENT for table `location_cities`
--
ALTER TABLE `location_cities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `location_countries`
--
ALTER TABLE `location_countries`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `location_states`
--
ALTER TABLE `location_states`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `membership_plans`
--
ALTER TABLE `membership_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `membership_transactions`
--
ALTER TABLE `membership_transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pages_vendor`
--
ALTER TABLE `pages_vendor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `payment_settings`
--
ALTER TABLE `payment_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_details`
--
ALTER TABLE `product_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_license_keys`
--
ALTER TABLE `product_license_keys`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_options`
--
ALTER TABLE `product_options`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_option_values`
--
ALTER TABLE `product_option_values`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_option_variants`
--
ALTER TABLE `product_option_variants`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_option_variant_values`
--
ALTER TABLE `product_option_variant_values`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_search_indexes`
--
ALTER TABLE `product_search_indexes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_settings`
--
ALTER TABLE `product_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `product_tags`
--
ALTER TABLE `product_tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promoted_transactions`
--
ALTER TABLE `promoted_transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quote_requests`
--
ALTER TABLE `quote_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refund_requests`
--
ALTER TABLE `refund_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refund_requests_messages`
--
ALTER TABLE `refund_requests_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles_permissions`
--
ALTER TABLE `roles_permissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_delivery_times`
--
ALTER TABLE `shipping_delivery_times`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_zones`
--
ALTER TABLE `shipping_zones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_zone_locations`
--
ALTER TABLE `shipping_zone_locations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_zone_methods`
--
ALTER TABLE `shipping_zone_methods`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `slider`
--
ALTER TABLE `slider`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_subtickets`
--
ALTER TABLE `support_subtickets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_membership_plans`
--
ALTER TABLE `users_membership_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_login_activities`
--
ALTER TABLE `user_login_activities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_deposits`
--
ALTER TABLE `wallet_deposits`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_expenses`
--
ALTER TABLE `wallet_expenses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
