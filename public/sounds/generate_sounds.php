<?php

function generateWav($filename, $frequencyOrNotes, $durationSeconds = 0.2, $volumePercent = 5, $sampleRate = 44100) {
    $numSamples = $sampleRate * $durationSeconds;
    $data = '';

    for ($i = 0; $i < $numSamples; $i++) {
        $t = $i / $sampleRate;
        
        // Envelope: soft attack and decay to prevent pops/clicks
        $envelope = 1.0;
        $attack = 0.005;
        $decay = $durationSeconds - $attack;
        if ($t < $attack) {
            $envelope = $t / $attack;
        } else {
            $envelope = 1.0 - (($t - $attack) / $decay);
        }
        
        $envelope = max(0, min(1, $envelope));

        $val = 0;
        if (is_numeric($frequencyOrNotes)) {
            // Single sine wave
            $val = sin(2 * M_PI * $frequencyOrNotes * $t);
        } elseif (is_array($frequencyOrNotes)) {
            // Chord (mix frequencies)
            foreach ($frequencyOrNotes as $freq) {
                $val += sin(2 * M_PI * $freq * $t);
            }
            $val = $val / count($frequencyOrNotes);
        } elseif (is_callable($frequencyOrNotes)) {
            // Dynamic function (e.g. sweep)
            $val = $frequencyOrNotes($t);
        }

        // Scale by volume (max 32767 in 16-bit signed PCM)
        $scaled = $val * 32767 * ($volumePercent / 100) * $envelope;
        $intVal = (int)max(-32768, min(32767, $scaled));
        
        // Pack as signed 16-bit little-endian
        $data .= pack('v', $intVal & 0xFFFF);
    }

    // WAV Header
    $dataLen = strlen($data);
    $header = "RIFF";
    $header .= pack('V', 36 + $dataLen); // Chunk size
    $header .= "WAVE";
    $header .= "fmt ";
    $header .= pack('V', 16); // Subchunk1Size (16 for PCM)
    $header .= pack('v', 1);  // AudioFormat (1 for PCM)
    $header .= pack('v', 1);  // NumChannels (1 for mono)
    $header .= pack('V', $sampleRate); // SampleRate
    $header .= pack('V', $sampleRate * 2); // ByteRate (SampleRate * NumChannels * BitsPerSample/8)
    $header .= pack('v', 2);  // BlockAlign (NumChannels * BitsPerSample/8)
    $header .= pack('v', 16);  // BitsPerSample (16 bits)
    $header .= "data";
    $header .= pack('V', $dataLen); // Subchunk2Size

    file_put_contents($filename, $header . $data);
}

// 1. General notification.wav: A soft, elegant warm major-triad chime (C5, E5, G5) with arpeggiated entry
$notifFunc = function($t) {
    $val = 0;
    if ($t < 0.35) {
        $env1 = 1 - ($t / 0.35);
        $val += sin(2 * M_PI * 523.25 * $t) * $env1;
    }
    if ($t >= 0.05 && $t < 0.35) {
        $t2 = $t - 0.05;
        $env2 = 1 - ($t2 / 0.30);
        $val += sin(2 * M_PI * 659.25 * $t2) * $env2;
    }
    if ($t >= 0.10 && $t < 0.35) {
        $t3 = $t - 0.10;
        $env3 = 1 - ($t3 / 0.25);
        $val += sin(2 * M_PI * 783.99 * $t3) * $env3;
    }
    return $val / 3.0;
};
generateWav(__DIR__ . '/notification.wav', $notifFunc, 0.35, 2.5);

// 2. reception.wav: A soft, pleasant warm double-ping (Chat Message Received)
$receptionFunc = function($t) {
    $val = 0;
    if ($t < 0.18) {
        $env1 = 1 - ($t / 0.18);
        $val += sin(2 * M_PI * 659.25 * $t) * $env1;
    }
    if ($t >= 0.07 && $t < 0.25) {
        $t2 = $t - 0.07;
        $env2 = 1 - ($t2 / 0.18);
        $val += sin(2 * M_PI * 880.0 * $t2) * $env2;
    }
    return $val / 2.0;
};
generateWav(__DIR__ . '/reception.wav', $receptionFunc, 0.25, 2.0);

// 3. envoi.wav: A short, rising frequency slide pop/zip (Chat Message Sent)
$envoiFunc = function($t) {
    // slide from 450Hz to 650Hz over 0.07 seconds
    return sin(2 * M_PI * (450 * $t + (100 / 0.07) * $t * $t));
};
generateWav(__DIR__ . '/envoi.wav', $envoiFunc, 0.07, 2.5);

// 4. alerte.wav: A gentle "da-dum" low warning tone for alerts
$alerteFunc = function($t) {
    $val = 0;
    if ($t < 0.25) {
        $env1 = 1 - ($t / 0.25);
        $val += sin(2 * M_PI * 349.23 * $t) * $env1;
    }
    if ($t >= 0.08 && $t < 0.32) {
        $t2 = $t - 0.08;
        $env2 = 1 - ($t2 / 0.24);
        $val += sin(2 * M_PI * 293.66 * $t2) * $env2;
    }
    return $val / 2.0;
};
generateWav(__DIR__ . '/alerte.wav', $alerteFunc, 0.32, 3.5);

echo "SUCCESS: Sounds generated successfully!\n";
