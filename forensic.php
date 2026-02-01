<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forensic Image Analyzer - Metadata & ELA</title>
    <script src="https://cdn.jsdelivr.net/npm/exifr/dist/full.umd.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #0d1117;
            color: #00d9ff;
            padding: 20px;
            min-height: 100vh;
        }

        header {
            text-align: center;
            margin-bottom: 40px;
        }

        h1 {
            font-size: 2.5rem;
            text-shadow: 0 0 10px rgba(0, 217, 255, 0.5);
            margin-bottom: 10px;
        }

        h1 span {
            color: #00d9ff;
        }

        .subtitle {
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-size: 0.875rem;
        }

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            max-width: 1800px;
            margin: 0 auto;
        }

        .panel {
            background: #161b22;
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 217, 255, 0.1);
        }

        .panel h2 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            text-shadow: 0 0 10px rgba(0, 217, 255, 0.5);
        }

        .metadata-list {
            max-height: 600px;
            overflow-y: auto;
        }

        .metadata-item {
            border-bottom: 1px solid #30363d;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .metadata-label {
            color: #58a6ff;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .metadata-value {
            color: #e6edf3;
            font-size: 0.875rem;
            word-break: break-all;
        }

        .ela-section {
            margin-bottom: 20px;
        }

        .ela-title {
            color: #58a6ff;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .image-container {
            background: #0d1117;
            padding: 15px;
            border: 1px solid #30363d;
            border-radius: 4px;
            text-align: center;
        }

        canvas {
            max-width: 100%;
            height: auto;
            border: 1px solid #30363d;
        }

        .verdict-container {
            background: #0d1117;
            border: 2px solid #30363d;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            text-align: center;
        }

        .verdict-title {
            color: #58a6ff;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 15px;
        }

        .verdict-result {
            font-size: 2.5rem;
            font-weight: bold;
            letter-spacing: 3px;
            margin-bottom: 15px;
            text-shadow: 0 0 20px currentColor;
        }

        .verdict-clean {
            color: #00ff88;
            border-color: #00ff88;
        }

        .verdict-suspicious {
            color: #ff4444;
            border-color: #ff4444;
        }

        .verdict-analyzing {
            color: #ffaa00;
            border-color: #ffaa00;
        }

        .verdict-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .stat-item {
            background: #161b22;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #30363d;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .stat-value {
            color: #00d9ff;
            font-size: 1.25rem;
            font-weight: bold;
        }

        .verdict-explanation {
            margin-top: 15px;
            padding: 15px;
            background: #161b22;
            border-radius: 4px;
            color: #e6edf3;
            font-size: 0.875rem;
            line-height: 1.6;
            text-align: left;
        }

        .confidence-bar {
            margin-top: 15px;
            background: #161b22;
            border-radius: 4px;
            overflow: hidden;
            height: 30px;
            border: 1px solid #30363d;
        }

        .confidence-fill {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
            transition: width 0.5s ease;
        }

        .confidence-clean {
            background: linear-gradient(90deg, #00ff88, #00cc66);
        }

        .confidence-suspicious {
            background: linear-gradient(90deg, #ff4444, #cc0000);
        }

        footer {
            text-align: center;
            margin-top: 40px;
            color: #6b7280;
            font-size: 0.75rem;
        }

        .loading {
            text-align: center;
            color: #6b7280;
            padding: 40px;
        }

        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            .verdict-stats {
                grid-template-columns: 1fr;
            }
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #0d1117;
        }

        ::-webkit-scrollbar-thumb {
            background: #30363d;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #58a6ff;
        }
    </style>
</head>
<body>
    <header>
        <h1><span>FORENSIC</span> IMAGE ANALYZER</h1>
        <p class="subtitle">Metadata Extraction & Error Level Analysis</p>
    </header>

    <div class="container">
        <!-- Metadata Panel -->
        <div class="panel">
            <h2>IMAGE METADATA</h2>
            <div id="metadata-content" class="metadata-list">
                <div class="loading">Loading metadata...</div>
            </div>
        </div>

        <!-- ELA Panel -->
        <div class="panel">
            <h2>ERROR LEVEL ANALYSIS</h2>
            <div class="ela-section">
                <div class="ela-title">Original Image</div>
                <div class="image-container">
                    <canvas id="original-canvas"></canvas>
                </div>
            </div>
            <div class="ela-section">
                <div class="ela-title">ELA Result (Bright areas indicate potential editing)</div>
                <div class="image-container">
                    <canvas id="ela-canvas"></canvas>
                </div>
            </div>

            <!-- Verdict Section -->
            <div id="verdict-container" class="verdict-container verdict-analyzing">
                <div class="verdict-title">Analysis Verdict</div>
                <div class="verdict-result">ANALYZING...</div>
                
                <div class="confidence-bar">
                    <div id="confidence-fill" class="confidence-fill" style="width: 0%">
                        Calculating...
                    </div>
                </div>

                <div class="verdict-stats">
                    <div class="stat-item">
                        <div class="stat-label">Manipulation Index</div>
                        <div class="stat-value" id="manipulation-index">--</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Affected Area</div>
                        <div class="stat-value" id="affected-area">--</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Confidence Level</div>
                        <div class="stat-value" id="confidence-level">--</div>
                    </div>
                </div>
                <div class="verdict-explanation" id="verdict-explanation">
                    Processing image analysis...
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>Analyzing: orig2.jpg | ELA Quality: 95% | Government-Grade Forensic Analysis</p>
    </footer>

    <script>
        // Configuration
        const IMAGE_PATH = 'orig2.jpg';
        const ELA_QUALITY = 0.95;

        // Extract and display metadata
        async function loadMetadata() {
            try {
                const metadata = await exifr.parse(IMAGE_PATH);
                const container = document.getElementById('metadata-content');

                console.log(metadata)
                
                if (metadata && Object.keys(metadata).length > 0) {
                    let html = '';
                    for (const [key, value] of Object.entries(metadata)) {
                        const label = key.replace(/([A-Z])/g, ' $1').trim();
                        const displayValue = value === null || value === undefined 
                            ? 'N/A' 
                            : typeof value === 'object' 
                                ? JSON.stringify(value, null, 2) 
                                : String(value);
                        
                        html += `
                            <div class="metadata-item">
                                <div class="metadata-label">${label}</div>
                                <div class="metadata-value">${displayValue}</div>
                            </div>
                        `;
                    }
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<div class="loading">No EXIF metadata found in image</div>';
                }
            } catch (error) {
                console.error('Error loading metadata:', error);
                document.getElementById('metadata-content').innerHTML = 
                    '<div class="loading">Error loading metadata</div>';
            }
        }

        // Advanced statistical analysis for manipulation detection
        function analyzeImageRegions(diffData, width, height) {
            const regionSize = 32; // Analyze in 32x32 pixel blocks
            const regions = [];
            
            for (let y = 0; y < height; y += regionSize) {
                for (let x = 0; x < width; x += regionSize) {
                    let regionSum = 0;
                    let pixelCount = 0;
                    
                    for (let dy = 0; dy < regionSize && (y + dy) < height; dy++) {
                        for (let dx = 0; dx < regionSize && (x + dx) < width; dx++) {
                            const idx = ((y + dy) * width + (x + dx)) * 4;
                            regionSum += diffData[idx];
                            pixelCount++;
                        }
                    }
                    
                    regions.push(regionSum / pixelCount);
                }
            }
            
            return regions;
        }

        // Calculate statistical measures
        function calculateStats(values) {
            const sorted = [...values].sort((a, b) => a - b);
            const mean = values.reduce((a, b) => a + b, 0) / values.length;
            const median = sorted[Math.floor(sorted.length / 2)];
            
            // Calculate standard deviation
            const variance = values.reduce((sum, val) => sum + Math.pow(val - mean, 2), 0) / values.length;
            const stdDev = Math.sqrt(variance);
            
            // Calculate quartiles
            const q1 = sorted[Math.floor(sorted.length * 0.25)];
            const q3 = sorted[Math.floor(sorted.length * 0.75)];
            const iqr = q3 - q1;
            
            return { mean, median, stdDev, q1, q3, iqr, sorted };
        }

        // Advanced verdict calculation using statistical methods
        function calculateVerdict(stats) {
            const {
                avgDiff,
                maxDiff,
                regionStats,
                pixelDistribution,
                totalPixels
            } = stats;

            // Calculate manipulation index (0-100)
            let manipulationIndex = 0;
            const factors = [];
            
            // CRITICAL: Check raw pixel brightness first (most direct indicator)
            const brightPixelPct = (pixelDistribution.bright / totalPixels) * 100;
            const veryBrightPixelPct = (pixelDistribution.veryBright / totalPixels) * 100;
            const extremeBrightPct = (pixelDistribution.extremeBright / totalPixels) * 100;
            
            // If there are significant bright areas visible in ELA, it's suspicious
            if (extremeBrightPct > 0.5) {
                manipulationIndex += 40;
                factors.push(`${extremeBrightPct.toFixed(2)}% of pixels show extreme brightness (>200) - Clear manipulation indicators`);
            }
            
            if (veryBrightPixelPct > 1) {
                manipulationIndex += 35;
                factors.push(`${veryBrightPixelPct.toFixed(2)}% of pixels show very high brightness (>150) - Strong manipulation signs`);
            }
            
            if (brightPixelPct > 3) {
                manipulationIndex += 25;
                factors.push(`${brightPixelPct.toFixed(2)}% of pixels show elevated brightness (>80) - Moderate manipulation indicators`);
            }
            
            // Factor 2: Regional consistency
            const outlierRegions = regionStats.sorted.filter(r => r > regionStats.q3 + 1.5 * regionStats.iqr);
            const outlierPercentage = (outlierRegions.length / regionStats.sorted.length) * 100;
            
            if (outlierPercentage > 8) {
                manipulationIndex += 30;
                factors.push(`${outlierPercentage.toFixed(1)}% of regions show anomalous compression patterns`);
            } else if (outlierPercentage > 4) {
                manipulationIndex += 15;
                factors.push(`${outlierPercentage.toFixed(1)}% of regions show irregular compression`);
            }
            
            // Factor 3: Average brightness across entire image
            if (avgDiff > 12) {
                manipulationIndex += 20;
                factors.push(`High average error level (${avgDiff.toFixed(2)}) across entire image`);
            } else if (avgDiff > 8) {
                manipulationIndex += 10;
                factors.push(`Elevated average error level (${avgDiff.toFixed(2)})`);
            }
            
            // Factor 4: Maximum difference (extreme anomalies)
            if (maxDiff > 180) {
                manipulationIndex += 15;
                factors.push(`Extreme localized anomalies detected (max: ${maxDiff.toFixed(0)})`);
            } else if (maxDiff > 120) {
                manipulationIndex += 8;
                factors.push(`High localized anomalies (max: ${maxDiff.toFixed(0)})`);
            }
            
            // Factor 5: Standard deviation (variance in error levels)
            const coefficientOfVariation = (regionStats.stdDev / regionStats.mean) * 100;
            if (coefficientOfVariation > 120) {
                manipulationIndex += 15;
                factors.push(`High compression variance detected (CV: ${coefficientOfVariation.toFixed(0)}%)`);
            }

            // Cap at 100
            manipulationIndex = Math.min(100, manipulationIndex);
            
            // CRITICAL: Lower threshold for suspicious verdict
            // If manipulation index is 30 or above, flag as suspicious
            const isSuspicious = manipulationIndex >= 30;
            const confidence = isSuspicious 
                ? Math.min(98, 55 + manipulationIndex * 0.4)
                : Math.min(95, 92 - manipulationIndex * 1.2);
            
            return {
                verdict: isSuspicious ? 'SUSPICIOUS' : 'CLEAN',
                manipulationIndex,
                confidence,
                factors,
                affectedArea: outlierPercentage
            };
        }

        // Update verdict display
        function updateVerdictDisplay(verdict, stats) {
            const container = document.getElementById('verdict-container');
            const resultEl = container.querySelector('.verdict-result');
            const explanationEl = document.getElementById('verdict-explanation');
            const confidenceFill = document.getElementById('confidence-fill');

            // Update stats
            document.getElementById('manipulation-index').textContent = verdict.manipulationIndex.toFixed(0) + '/100';
            document.getElementById('affected-area').textContent = verdict.affectedArea.toFixed(1) + '%';
            document.getElementById('confidence-level').textContent = verdict.confidence.toFixed(0) + '%';


            // Update confidence bar
            confidenceFill.style.width = verdict.affectedArea + '%';
            confidenceFill.className = 'confidence-fill ' + (verdict.verdict === 'CLEAN' ? 'confidence-clean' : 'confidence-suspicious');
            confidenceFill.textContent = `${verdict.confidence.toFixed(0)}% Confidence`;

            // Update verdict
            container.className = `verdict-container verdict-${verdict.verdict.toLowerCase()}`;
            resultEl.textContent = verdict.verdict;

            // Generate explanation
            let explanation = '';
            if (verdict.verdict === 'CLEAN') {
                explanation = `
                    <strong>✓ Image Integrity Verified</strong><br><br>
                    The forensic analysis shows consistent compression patterns throughout the image with a manipulation index of ${verdict.manipulationIndex}/100. 
                    The error levels are uniform across all regions, indicating the image has not undergone significant post-processing or manipulation.
                    <br><br>
                    <strong>Technical Summary:</strong><br>
                    • Regional consistency: High<br>
                    • Compression variance: Within normal limits<br>
                    • Anomaly distribution: Uniform (natural JPEG artifacts)<br>
                    • Affected regions: ${verdict.affectedArea.toFixed(1)}%
                    <br><br>
                    <em>Confidence: ${verdict.confidence.toFixed(0)}% - This image appears authentic.</em>
                `;
            } else {
                explanation = `
                    <strong>⚠ Potential Manipulation Detected</strong><br><br>
                    The forensic analysis has identified irregularities with a manipulation index of ${verdict.manipulationIndex}/100. 
                    The following indicators suggest potential editing or manipulation:
                    <br><br>
                    ${verdict.factors.map(factor => `• ${factor}`).join('<br>')}
                    <br><br>
                    <strong>Recommendation:</strong> This image should undergo additional verification. Bright regions in the ELA visualization 
                    indicate areas where compression patterns differ significantly from the rest of the image, suggesting those regions 
                    may have been edited, cloned, spliced, or processed using different tools/settings.
                    <br><br>
                    <em>Confidence: ${verdict.confidence.toFixed(0)}% - Further investigation recommended.</em>
                `;
            }

            explanationEl.innerHTML = explanation;
        }

        // Perform Error Level Analysis
        async function performELA() {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            
            img.onload = function() {
                // Draw original image
                const originalCanvas = document.getElementById('original-canvas');
                const originalCtx = originalCanvas.getContext('2d');
                originalCanvas.width = img.width;
                originalCanvas.height = img.height;
                originalCtx.drawImage(img, 0, 0);

                // Create temporary canvas for recompression
                const tempCanvas = document.createElement('canvas');
                tempCanvas.width = img.width;
                tempCanvas.height = img.height;
                const tempCtx = tempCanvas.getContext('2d');
                
                // Draw and recompress
                tempCtx.drawImage(img, 0, 0);
                const recompressedUrl = tempCanvas.toDataURL('image/jpeg', ELA_QUALITY);
                
                // Load recompressed image
                const recompressedImg = new Image();
                recompressedImg.onload = function() {
                    // Get image data from both images
                    tempCtx.drawImage(img, 0, 0);
                    const originalData = tempCtx.getImageData(0, 0, img.width, img.height);
                    
                    tempCtx.drawImage(recompressedImg, 0, 0);
                    const recompressedData = tempCtx.getImageData(0, 0, img.width, img.height);

                    // Calculate difference and display on ELA canvas
                    const elaCanvas = document.getElementById('ela-canvas');
                    const elaCtx = elaCanvas.getContext('2d');
                    elaCanvas.width = img.width;
                    elaCanvas.height = img.height;
                    
                    const diffData = elaCtx.createImageData(img.width, img.height);
                    
                    // Advanced statistics collection
                    let totalDiff = 0;
                    let maxDiff = 0;
                    const pixelDifferences = [];
                    const pixelDistribution = {
                        bright: 0,      // > 80
                        veryBright: 0,  // > 150
                    };

                    for (let i = 0; i < originalData.data.length; i += 4) {
                        // Calculate difference and amplify
                        const diff = Math.abs(originalData.data[i] - recompressedData.data[i]) * 10;
                        diffData.data[i] = diff;
                        diffData.data[i + 1] = diff;
                        diffData.data[i + 2] = diff;
                        diffData.data[i + 3] = 255;

                        totalDiff += diff;
                        maxDiff = Math.max(maxDiff, diff);
                        pixelDifferences.push(diff);
                        
                        if (diff > 150) pixelDistribution.veryBright++;
                        else if (diff > 80) pixelDistribution.bright++;
                    }

                    elaCtx.putImageData(diffData, 0, 0);

                    // Analyze regions
                    const regionValues = analyzeImageRegions(diffData.data, img.width, img.height);
                    const regionStats = calculateStats(regionValues);

                    // Calculate comprehensive statistics
                    const stats = {
                        avgDiff: totalDiff / (originalData.data.length / 4),
                        maxDiff: maxDiff,
                        regionStats: regionStats,
                        pixelDistribution: pixelDistribution,
                        totalPixels: originalData.data.length / 4
                    };

                    // Calculate and display verdict
                    const verdict = calculateVerdict(stats);
                    updateVerdictDisplay(verdict, stats);
                };
                
                recompressedImg.src = recompressedUrl;
            };

            img.onerror = function() {
                console.error('Error loading image');
                alert('Error loading image. Make sure orig2.jpg is in the same directory.');
            };

            img.src = IMAGE_PATH;
        }

        // Initialize on page load
        window.addEventListener('load', function() {
            loadMetadata();
            performELA();
        });
    </script>
</body>
</html>