<div class="product-audio-preview">
    <div id="single-song-player">
        <img data-amplitude-song-info="cover_art_url" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="/>
    </div>

    <div class="player-control-container">
        <div class="bottom-container">
            <progress class="amplitude-song-played-progress" id="song-played-progress"></progress>
            <div class="time-container">
                    <span class="current-time">
                        <span class="amplitude-current-minutes">0</span>:<span class="amplitude-current-seconds">00</span>
                    </span>
                <span class="duration">
                    <span class="amplitude-duration-minutes">3</span>:<span class="amplitude-duration-seconds">45</span>
               </span>
            </div>
        </div>

        <div class="controls-buttons">
            <button class="player-btn btn-side" onclick="skipBackward();" aria-label="button backward">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16" height="16" fill="currentColor">
                    <path d="M236.3 107.1C247.9 96 265 92.9 279.7 99.2C294.4 105.5 304 120 304 136L304 272.3L476.3 107.2C487.9 96 505 92.9 519.7 99.2C534.4 105.5 544 120 544 136L544 504C544 520 534.4 534.5 519.7 540.8C505 547.1 487.9 544 476.3 532.9L304 367.7L304 504C304 520 294.4 534.5 279.7 540.8C265 547.1 247.9 544 236.3 532.9L44.3 348.9C36.5 341.3 32 330.9 32 320C32 309.1 36.5 298.7 44.3 291.1L236.3 107.1z"/>
                </svg>
            </button>

            <button class="player-btn btn-main amplitude-play-pause" id="play-pause-btn" aria-label="button play-pause">
                <svg id="play-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16" height="16" fill="currentColor">
                    <path d="M187.2 100.9C174.8 94.1 159.8 94.4 147.6 101.6C135.4 108.8 128 121.9 128 136L128 504C128 518.1 135.5 531.2 147.6 538.4C159.7 545.6 174.8 545.9 187.2 539.1L523.2 355.1C536 348.1 544 334.6 544 320C544 305.4 536 291.9 523.2 284.9L187.2 100.9z"/>
                </svg>

                <svg id="pause-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16" height="16" fill="currentColor">
                    <path d="M176 96C149.5 96 128 117.5 128 144L128 496C128 522.5 149.5 544 176 544L240 544C266.5 544 288 522.5 288 496L288 144C288 117.5 266.5 96 240 96L176 96zM400 96C373.5 96 352 117.5 352 144L352 496C352 522.5 373.5 544 400 544L464 544C490.5 544 512 522.5 512 496L512 144C512 117.5 490.5 96 464 96L400 96z"/>
                </svg>
            </button>

            <button class="player-btn btn-side" onclick="skipForward();" aria-label="button forward">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16" height="16" fill="currentColor">
                    <path d="M403.7 107.1C392.1 96 375 92.9 360.3 99.2C345.6 105.5 336 120 336 136L336 272.3L163.7 107.2C152.1 96 135 92.9 120.3 99.2C105.6 105.5 96 120 96 136L96 504C96 520 105.6 534.5 120.3 540.8C135 547.1 152.1 544 163.7 532.9L336 367.7L336 504C336 520 345.6 534.5 360.3 540.8C375 547.1 392.1 544 403.7 532.9L595.7 348.9C603.6 341.4 608 330.9 608 320C608 309.1 603.5 298.7 595.7 291.1L403.7 107.1z"/>
                </svg>
            </button>
        </div>

    </div>
</div>

<link rel="stylesheet" href="<?= base_url('assets/vendor/amplitudejs/app.min.css'); ?>"/>
<script src="<?= base_url('assets/vendor/amplitudejs/amplitude.min.js'); ?>"></script>
<script>
    Amplitude.init({
        "songs": [
            {
                "name": "",
                "artist": "",
                "album": "",
                "url": "<?= getProductAudioUrl($audio); ?>",
                "cover_art_url": "<?= getProductMainImage($product->id, 'image_big'); ?>"
            }
        ]
    });
    document.getElementById('song-played-progress').addEventListener('click', function (e) {
        var offset = this.getBoundingClientRect();
        var x = e.pageX - offset.left;
        Amplitude.setSongPlayedPercentage((parseFloat(x) / parseFloat(this.offsetWidth)) * 100);
    });

    function skipBackward() {
        var progress = $('#song-played-progress').val();
        var progress = progress * 100;
        var newProgress = progress - 5;
        Amplitude.setSongPlayedPercentage(newProgress);
    }

    function skipForward() {
        var progress = $('#song-played-progress').val();
        var progress = progress * 100;
        var newProgress = progress + 5;
        Amplitude.setSongPlayedPercentage(newProgress);
    }
</script>

