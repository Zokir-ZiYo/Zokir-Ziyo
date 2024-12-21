<?php
// Define the path to the music folder
$music_folder = 'music';

// Fetch the list of MP3 files in the folder
$songs = array_diff(scandir($music_folder), array('.', '..'));
$songs = array_filter($songs, function($file) use ($music_folder) {
    return pathinfo($file, PATHINFO_EXTENSION) === 'mp3';
});
$songs = array_values($songs);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP MP3 Player</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }
        .player {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 400px;
            padding: 25px;
            text-align: center;
            backdrop-filter: blur(10px);
            overflow: hidden;
        }
        .player h2 {
            margin: 0;
            margin-bottom: 20px;
            font-size: 1.5em;
            letter-spacing: 1px;
        }
        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .controls button, .volume-control {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .controls button:hover, .volume-control:hover {
            background: rgba(255, 255, 255, 0.4);
        }
        .progress-container {
            position: relative;
            background: rgba(255, 255, 255, 0.2);
            height: 5px;
            margin: 10px 0;
            border-radius: 5px;
            cursor: pointer;
        }
        .progress {
            background: #fff;
            height: 100%;
            width: 0;
            border-radius: 5px;
        }
        .details {
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        .details .time {
            font-size: 14px;
            font-weight: bold;
        }
        .player ul {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 220px;
            overflow-y: auto;
        }
        .player ul li {
            padding: 12px;
            margin-bottom: 5px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }
        .player ul li:hover {
            background: rgba(255, 255, 255, 0.4);
            transform: scale(1.05);
        }
        .player ul li.active {
            background: rgba(0, 123, 255, 0.8);
            color: #fff;
        }
        .scrollbar-hidden {
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* Internet Explorer */
        }
        .scrollbar-hidden::-webkit-scrollbar {
            display: none; /* Chrome, Safari */
        }
        .volume-control {
            width: 100px;
            text-align: center;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="player">
        <h2>PHP MP3 Player</h2>
        <div class="controls">
            <button id="prev">&#9664; Prev</button>
            <button id="play">&#9658; Play</button>
            <button id="next">Next &#9654;</button>
        </div>
        <div class="details">
            <span class="time">
                <span id="current-time">0:00</span> / <span id="total-time">0:00</span>
            </span>
        </div>
        <div class="progress-container" id="progress-container">
            <div class="progress" id="progress"></div>
        </div>
        <div class="volume-control">
            <span id="volume-level">🔊</span>
            <input type="range" id="volume" min="0" max="1" step="0.01" value="1">
        </div>
        <audio id="audio">
            <source id="audio-source" src="<?php echo $music_folder . '/' . $songs[0]; ?>" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        <ul id="playlist" class="scrollbar-hidden">
            <?php foreach ($songs as $index => $song): ?>
                <li data-index="<?php echo $index; ?>" data-src="<?php echo $music_folder . '/' . $song; ?>">
                    <?php echo pathinfo($song, PATHINFO_FILENAME); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
        const audio = document.getElementById('audio');
        const playlist = document.getElementById('playlist');
        const listItems = playlist.querySelectorAll('li');
        const playButton = document.getElementById('play');
        const nextButton = document.getElementById('next');
        const prevButton = document.getElementById('prev');
        const progressContainer = document.getElementById('progress-container');
        const progress = document.getElementById('progress');
        const currentTimeEl = document.getElementById('current-time');
        const totalTimeEl = document.getElementById('total-time');
        const volumeSlider = document.getElementById('volume');
        const volumeLevel = document.getElementById('volume-level');

        let currentIndex = 0;
        let isPlaying = false;

        function playAudio() {
            audio.play();
            playButton.textContent = '⏸ Pause';
            isPlaying = true;
        }

        function pauseAudio() {
            audio.pause();
            playButton.textContent = '▶ Play';
            isPlaying = false;
        }

        playButton.addEventListener('click', () => {
            if (isPlaying) {
                pauseAudio();
            } else {
                playAudio();
            }
        });

        nextButton.addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % listItems.length;
            updateSong();
            playAudio();
        });

        prevButton.addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + listItems.length) % listItems.length;
            updateSong();
            playAudio();
        });

        playlist.addEventListener('click', (event) => {
            if (event.target.tagName === 'LI') {
                currentIndex = parseInt(event.target.getAttribute('data-index'));
                updateSong();
                playAudio();
            }
        });

        audio.addEventListener('timeupdate', () => {
            const currentTime = formatTime(audio.currentTime);
            const duration = formatTime(audio.duration);

            currentTimeEl.textContent = currentTime;
            totalTimeEl.textContent = duration ? duration : '0:00';

            const progressPercent = (audio.currentTime / audio.duration) * 100;
            progress.style.width = `${progressPercent}%`;
        });

        progressContainer.addEventListener('click', (e) => {
            const width = progressContainer.clientWidth;
            const clickX = e.offsetX;
            const duration = audio.duration;

            audio.currentTime = (clickX / width) * duration;
        });

        audio.addEventListener('ended', () => {
            currentIndex = (currentIndex + 1) % listItems.length;
            updateSong();
            playAudio();
        });

        volumeSlider.addEventListener('input', () => {
            audio.volume = volumeSlider.value;
            if (audio.volume === 0) {
                volumeLevel.textContent = '🔈';
            } else if (audio.volume < 0.5) {
                volumeLevel.textContent = '🔉';
            } else {
                volumeLevel.textContent = '🔊';
            }
        });

        function updateSong() {
            const song = listItems[currentIndex];
            audio.src = song.getAttribute('data-src');
            highlightCurrentSong();
        }

        function highlightCurrentSong() {
            listItems.forEach((item, index) => {
                item.classList.toggle('active', index === currentIndex);
            });
        }

        function formatTime(time) {
            const minutes = Math.floor(time / 60);
            const seconds = Math.floor(time % 60);
            return `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        }

        // Initialize
        highlightCurrentSong();
    </script>
</body>
</html>
