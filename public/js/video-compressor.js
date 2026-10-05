/**
 * VideoCompressor - Client-Side Video Compression using FFmpeg.wasm
 * 
 * Uses the single-threaded build of FFmpeg.wasm (@ffmpeg/core-st) to eliminate
 * the need for SharedArrayBuffer and strict Cross-Origin Isolation (COOP/COEP) headers.
 * 
 * Features:
 * - Automatically rescales to max 720p HD maintaining aspect ratio with even dimensions
 * - Fast H.264 encoding with x264 'ultrafast' preset for rapid browser execution
 * - AAC 128k audio encoding with automatic audio fallback (-an) for silent videos
 * - Web-optimized MP4 with +faststart for immediate streaming playback
 * - Detailed real-time progress callbacks (0-100%) via ffmpeg.setProgress
 * - In-memory virtual filesystem cleanup
 * - Automatic size comparison: keeps original file if output is larger
 */

(function(window) {
    'use strict';

    let ffmpegInstance = null;
    let isFFmpegLoading = false;

    const FFMPEG_CORE_ST_URL = 'https://cdn.jsdelivr.net/npm/@ffmpeg/core-st@0.11.1/dist/ffmpeg-core.js';
    const FFMPEG_LIB_URL = 'https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.11.6/dist/ffmpeg.min.js';

    /**
     * Checks if a file is a video by MIME type or extension.
     */
    function isVideo(file) {
        if (!file) return false;
        return (file.type && file.type.startsWith('video/')) || 
               /\.(mp4|webm|ogg|mov|mkv|avi|m4v|3gp)$/i.test(file.name || '');
    }

    /**
     * Formats bytes into human-readable strings (e.g., "81.6 MB").
     */
    function formatBytes(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    /**
     * Dynamically loads FFmpeg.wasm script if not already on the page.
     */
    async function loadFFmpegScript() {
        if (typeof window.FFmpeg !== 'undefined') return true;

        return new Promise((resolve, reject) => {
            const existing = document.querySelector(`script[src*="ffmpeg.min.js"]`);
            if (existing) {
                existing.addEventListener('load', () => resolve(true));
                existing.addEventListener('error', (e) => reject(new Error('Failed to load FFmpeg.wasm CDN script')));
                return;
            }

            const script = document.createElement('script');
            script.src = FFMPEG_LIB_URL;
            script.async = true;
            script.onload = () => resolve(true);
            script.onerror = () => reject(new Error('Failed to load FFmpeg.wasm CDN script from jsDelivr'));
            document.head.appendChild(script);
        });
    }

    /**
     * Returns a loaded singleton FFmpeg instance.
     */
    async function getFFmpeg(onStatus) {
        if (ffmpegInstance && ffmpegInstance.isLoaded()) {
            return ffmpegInstance;
        }

        if (isFFmpegLoading) {
            while (isFFmpegLoading) {
                await new Promise(r => setTimeout(r, 100));
            }
            if (ffmpegInstance && ffmpegInstance.isLoaded()) return ffmpegInstance;
        }

        isFFmpegLoading = true;
        try {
            if (typeof onStatus === 'function') onStatus('Loading compression engine...');
            await loadFFmpegScript();

            if (typeof window.FFmpeg === 'undefined' || !window.FFmpeg.createFFmpeg) {
                throw new Error('FFmpeg UMD library could not be initialized.');
            }

            ffmpegInstance = window.FFmpeg.createFFmpeg({
                log: false,
                corePath: FFMPEG_CORE_ST_URL
            });

            await ffmpegInstance.load();
            return ffmpegInstance;
        } finally {
            isFFmpegLoading = false;
        }
    }

    /**
     * Compresses a video File or Blob.
     * 
     * @param {File|Blob} file - Original video file
     * @param {Object} options - Configuration options
     * @param {Function} options.onProgress - Callback with percentage number (0-100)
     * @param {Function} options.onStatus - Callback with human status string
     * @param {number} options.maxWidth - Max video width (default: 1280 for 720p)
     * @param {string} options.videoBitrate - Target video bitrate (default: '1M')
     * @param {number} options.crf - Constant rate factor quality (default: 28)
     * @returns {Promise<File>} Compressed File, or original file if compression failed/larger
     */
    async function compress(file, options = {}) {
        if (!file) return file;

        if (!isVideo(file)) {
            return file;
        }

        const {
            onProgress = () => {},
            onStatus = () => {},
            maxWidth = 1280,
            videoBitrate = '1M',
            crf = 28
        } = options;

        // Skip compression for tiny videos already under 2.5MB in MP4
        if (file.size < 2.5 * 1024 * 1024 && file.type === 'video/mp4') {
            console.log('[VideoCompressor] Video already small and mp4, skipping compression.');
            onProgress(100);
            return file;
        }

        onStatus('Initializing FFmpeg engine...');
        onProgress(2);

        let ffmpeg;
        try {
            ffmpeg = await getFFmpeg(onStatus);
        } catch (err) {
            console.warn('[VideoCompressor] Could not initialize FFmpeg.wasm:', err);
            onStatus('Compression unavailable, using original file.');
            return file;
        }

        // Attach live progress listener
        ffmpeg.setProgress(({ ratio }) => {
            if (typeof ratio === 'number' && !isNaN(ratio) && ratio >= 0) {
                // Map internal ratio (0.0 - 1.0) to 10% - 95%
                const pct = Math.min(95, Math.max(10, Math.round(ratio * 85) + 10));
                onProgress(pct);
                onStatus(`Compressing video... ${pct}%`);
            }
        });

        const id = Date.now() + '_' + Math.random().toString(36).substring(2, 7);
        const originalExt = (file.name ? file.name.split('.').pop() : 'mp4').toLowerCase().replace(/[^a-z0-9]/g, '');
        const inputName = `input_${id}.${originalExt || 'mp4'}`;
        const outputName = `output_${id}.mp4`;

        onStatus('Reading video file into memory...');
        onProgress(5);

        try {
            // Read binary data from File/Blob
            let fileData;
            if (window.FFmpeg && typeof window.FFmpeg.fetchFile === 'function') {
                fileData = await window.FFmpeg.fetchFile(file);
            } else if (file.arrayBuffer) {
                fileData = new Uint8Array(await file.arrayBuffer());
            } else {
                fileData = await new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = () => resolve(new Uint8Array(reader.result));
                    reader.onerror = reject;
                    reader.readAsArrayBuffer(file);
                });
            }

            ffmpeg.FS('writeFile', inputName, fileData);
            onProgress(10);
            onStatus('Running compression (720p H.264)...');

            // Primary FFmpeg run with audio
            let runSuccess = false;
            try {
                await ffmpeg.run(
                    '-i', inputName,
                    '-vf', `scale='min(${maxWidth},iw)':-2`,
                    '-vcodec', 'libx264',
                    '-crf', String(crf),
                    '-preset', 'ultrafast',
                    '-b:v', videoBitrate,
                    '-maxrate', '1.5M',
                    '-bufsize', '2M',
                    '-c:a', 'aac',
                    '-b:a', '128k',
                    '-movflags', '+faststart',
                    outputName
                );
                runSuccess = true;
            } catch (firstPassErr) {
                console.warn('[VideoCompressor] First pass with audio failed, retrying video-only (-an):', firstPassErr);
            }

            // Fallback pass: without audio (for silent clips or incompatible audio streams)
            if (!runSuccess) {
                try { ffmpeg.FS('unlink', outputName); } catch (e) {}
                await ffmpeg.run(
                    '-i', inputName,
                    '-vf', `scale='min(${maxWidth},iw)':-2`,
                    '-vcodec', 'libx264',
                    '-crf', String(crf),
                    '-preset', 'ultrafast',
                    '-b:v', videoBitrate,
                    '-maxrate', '1.5M',
                    '-bufsize', '2M',
                    '-an',
                    '-movflags', '+faststart',
                    outputName
                );
            }

            onStatus('Finalizing compressed video...');
            onProgress(97);

            const outData = ffmpeg.FS('readFile', outputName);
            if (!outData || outData.length === 0) {
                throw new Error('Compression produced empty output file.');
            }

            const compressedBlob = new Blob([outData.buffer], { type: 'video/mp4' });

            // If compressed file is unexpectedly larger than original, keep original
            if (compressedBlob.size >= file.size) {
                console.log(`[VideoCompressor] Original file is smaller (${file.size} vs ${compressedBlob.size}). Keeping original.`);
                onProgress(100);
                onStatus('Original file is already optimal.');
                return file;
            }

            const rawName = (file.name || 'video').replace(/\.[^/.]+$/, "");
            const compressedFile = new File([compressedBlob], `${rawName}_compressed.mp4`, {
                type: 'video/mp4',
                lastModified: Date.now()
            });

            onProgress(100);
            onStatus('Compression complete!');
            return compressedFile;

        } catch (err) {
            console.error('[VideoCompressor] Compression failed, falling back to original file:', err);
            onProgress(100);
            onStatus('Compression failed. Using original file.');
            return file;
        } finally {
            // Clean virtual filesystem
            try { ffmpeg.FS('unlink', inputName); } catch (e) {}
            try { ffmpeg.FS('unlink', outputName); } catch (e) {}
        }
    }

    // Expose to window
    window.VideoCompressor = {
        isVideo,
        formatBytes,
        compress,
        getFFmpeg,
        loadFFmpegScript
    };

})(window);
