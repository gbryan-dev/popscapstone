<?php require_once 'partials/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>
    <script src="https://cdn.jsdelivr.net/npm/exif-js"></script>
    <meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="format-detection" content="telephone=no">
<title>POPS - Pyrotechnic Online Permitting System | CSG</title>
<meta name="author" content="CSG - Civil Security Group">
<meta name="description" content="POPS is a streamlined online system designed to assist LGUs and constituents in managing permit processing efficiently, transparently, and digitally.">
<meta name="keywords" content="POPS, permitting, online processing, LGU, digital applications, CSG, governance, public service">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

<!-- FAVICON FILES -->
<link href="../../assets/images/logo.png" rel="apple-touch-icon" sizes="144x144">
<link href="../../assets/images/logo.png" rel="apple-touch-icon" sizes="120x120">
<link href="../../assets/images/logo.png" rel="apple-touch-icon" sizes="76x76">
<link href="../../assets/images/logo.png" rel="shortcut icon">

<link href="assets/plugins/font-awesome/css/all.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900&display=swap" rel="stylesheet">
<link href="assets/css/lime.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .header-bar { background-color: #343a40; color: white; padding: 20px 0; }
        .card { box-shadow: 0 4px 6px rgba(0,0,0,0.06); border: none; margin-bottom: 18px; }
        .canvas-container { width: 100%; text-align: center; background: #eee; padding: 10px; border-radius: 5px; }
        canvas { max-width: 100%; height: auto; box-shadow: 0 0 5px rgba(0,0,0,0.08); background: white; }
        .verdict-box { padding: 12px; border-radius: 6px; font-weight: 700; text-align: center; margin-bottom: 10px; }
        .risk-high { background-color: #f8d7da; color: #721c24; }
        .risk-med { background-color: #fff3cd; color: #856404; }
        .risk-low { background-color: #d4edda; color: #155724; }
        .small-muted { font-size: 0.85rem; color: #6c757d; }
        pre.json { background: #0f1724; color: #e6eef8; padding: 10px; border-radius: 6px; overflow:auto; max-height:200px; }
        .input-group-lg .form-control { font-size: 1rem; }
        .loading-spinner { display: none; }
        .loading-spinner.active { display: inline-block; }
        .file-type-badge { font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; margin-left: 8px; }
        .badge-image { background-color: #198754; color: white; }
        .badge-pdf { background-color: #dc3545; color: white; }
        .badge-unknown { background-color: #6c757d; color: white; }

        .button {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 4px 12px;
  gap: 8px;
  border: none;
  background: #ff362b34;
  border-radius: 20px;
  cursor: pointer;
}

.lable {
  line-height: 20px;
  font-size: 12px;
  color: #ff342b;
  letter-spacing: 1px;
}

.button:hover {
  background: #ff362b52;
}

.button:hover .svg-icon {
  animation: spin 2s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(-360deg);
  }
}


    </style>
</head>
<body>
         <div class="lime-header">
            <nav class="navbar navbar-expand-lg" style="display: flex;width: 100%;justify-content: space-between;">
                <section class="material-design-hamburger navigation-toggle">
<!-- From Uiverse.io by andrew-demchenk0 --> 
<button class="button" onclick="window.close();">
  
  <span class="lable">Close</span>
</button>

                </section>
                <img src="../images/logo.png" class="navbar-brand" style="height:100px;width: 100px;">
                <div>
                   <button class="button" onclick="window.close();">
  
  <span class="lable">Close</span>
</button>
                </div>
            </nav>
        </div>
  
    <div class="container" style="padding-top:90px">

        <div class="row justify-content-center " style="padding-top:90px">
            <div class="col-md-12">
                <div class="card p-3">

                    <div class="header-bar text-center">
        <h2>POPS' Digital Forensics</h2>
        <p class="mb-0 small-muted" style="color:white !important;font-weight: 400 !important;">Auto-Tamper Detection & ELA Visualization</p>
    </div>







                    <div style="display:none;">
                    <label for="fileUrlInput" class="form-label fw-bold">Enter File URL or Path (JPG, PNG, PDF)</label>
                    <div class="input-group input-group-lg mb-2">
                        <input type="hidden" class="form-control" id="fileUrlInput" 
                               placeholder="https://example.com/image.jpg or ./local-image.png"
                               aria-label="File URL or path">
                        <button class="btn btn-primary" type="button" id="analyzeBtn">
                            <span class="loading-spinner spinner-border spinner-border-sm me-1" role="status"></span>
                            Analyze
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="form-text small-muted">
                            Enter a URL to an image or PDF file. Supports: JPG, JPEG, PNG, PDF
                        </div>
                        <div id="fileTypeBadge"></div>
                    </div>
                    
                  

                    <hr class="my-3">
                    
                    <div class="small-muted" style="display:none">
                        <strong>Alternative:</strong> Select a local file
                        <input class="form-control form-control-sm mt-2" type="file" id="fileInput" accept=".jpg, .jpeg, .png, .pdf">
                    </div>
                </div>

                </div>
            </div>
        </div>

        <div id="errorAlert" class="row justify-content-center mb-3" style="display: none;">
            <div class="col-md-10">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> <span id="errorMessage"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="hideError()"></button>
                </div>
            </div>
        </div>

        <div class="row" id="resultsArea" >
            <div class="col-lg-4">
                <div class="card p-3">
                    <h5 class="card-title">Quick Summary</h5>
                    <div id="verdictBox" class="verdict-box risk-low">WAITING...</div>
                    <div class="d-flex justify-content-between small-muted mb-2">
                        <div id="riskScoreDisplay">Risk Score: 0/100</div>
                        <div id="elaScoreDisplay">ELA Risk: 0</div>
                    </div>

                    <div id="results" style="max-height: 680px; overflow-y:auto;overflow-x:hidden;"></div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card p-3">
                    <h4 class="card-title">Visual Forensics (ELA)</h4>
                    <p class="small-muted mb-2" style="display:none;">
                        <strong>Heatmap Mode:</strong> Dark areas are original. Bright/Red areas indicate larger compression differences.
                    </p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="mb-2">Original / Rendered</h6>
                            <div class="canvas-container">
                                <canvas id="canvasOrig"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="mb-2">ELA Visualization</h6>
                            <div class="canvas-container">
                                <canvas id="canvasELA"></canvas>
                            </div>
                        </div>
                    </div>

                    <div id="extra-debug" class="mt-2 small-muted"></div>
                </div>
            </div>
        </div>
    </div>

<script>
// ========== CONSTANTS ==========
const SOFTWARE_KEYWORDS = ['Photoshop','GIMP','Editor','Picsart','Modified','TouchUp','Gemini','Canva'];
const TRUSTED_KEYWORDS = ['Skia','Google Docs','iText','Directly Photographed','Daylight','PNP','Flash not fired'];
const MEDIAN_KEYWORDS = ['Acrobat Sign','Acrobat Pro','Adobe','iTEXT','VeryPDF'];

// ========== DOM ==========
const fileInput = document.getElementById('fileInput');
const fileUrlInput = document.getElementById('fileUrlInput');
const analyzeBtn = document.getElementById('analyzeBtn');
const canvasOrig = document.getElementById('canvasOrig');
const canvasELA = document.getElementById('canvasELA');
const verdictBox = document.getElementById('verdictBox');
const riskScoreDisplay = document.getElementById('riskScoreDisplay');
const elaScoreDisplay = document.getElementById('elaScoreDisplay');
const resultsDiv = document.getElementById('results');
const extraDebug = document.getElementById('extra-debug');
const fileTypeBadge = document.getElementById('fileTypeBadge');
const errorAlert = document.getElementById('errorAlert');
const errorMessage = document.getElementById('errorMessage');
const loadingSpinner = document.querySelector('.loading-spinner');

let riskLog = [];

// ========== HELPERS ==========
function logRisk(reason, value) {
    riskLog.push({ reason, value });
}

function showError(msg) {
    errorMessage.textContent = msg;
    errorAlert.style.display = 'block';
}

function hideError() {
    errorAlert.style.display = 'none';
}

function setLoading(isLoading) {
    analyzeBtn.disabled = isLoading;
    loadingSpinner.classList.toggle('active', isLoading);
}

function getFileType(url) {
    const lower = url.toLowerCase();
    if (lower.endsWith('.pdf')) return 'pdf';
    if (lower.endsWith('.jpg') || lower.endsWith('.jpeg')) return 'image';
    if (lower.endsWith('.png')) return 'image';
    // Try to detect from URL patterns
    if (lower.includes('.pdf')) return 'pdf';
    if (lower.includes('.jpg') || lower.includes('.jpeg') || lower.includes('.png')) return 'image';
    return 'unknown';
}

function updateFileTypeBadge(url) {
    const type = getFileType(url);
    let badge = '';
    if (type === 'image') {
        badge = '<span class="file-type-badge badge-image">IMAGE</span>';
    } else if (type === 'pdf') {
        badge = '<span class="file-type-badge badge-pdf">PDF</span>';
    } else if (url.trim()) {
        badge = '<span class="file-type-badge badge-unknown">UNKNOWN</span>';
    }
    fileTypeBadge.innerHTML = badge;
}

// keyword scoring (returns numeric)
function keywordScore(key, value, usedKeywords) {
    let score = 0;
    const lower = String(value).toLowerCase();
    let positiveHits = 0, trustedHits = 0;

    for (let w of SOFTWARE_KEYWORDS) {
        const wl = w.toLowerCase();
        if (lower.includes(wl) && !usedKeywords.has(wl)) {
            usedKeywords.add(wl);
            score += 50;
            positiveHits++;
            logRisk(`Detected editing software keyword: "${w}"`, +50);
        }
    }

    for (let w of TRUSTED_KEYWORDS) {
        const wl = w.toLowerCase();
        if (lower.includes(wl) && !usedKeywords.has(wl)) {
            usedKeywords.add(wl);
            score -= 40;
            trustedHits++;
            logRisk(`Detected trusted keyword: "${w}"`, -40);
        }
    }

    for (let w of MEDIAN_KEYWORDS) {
        const wl = w.toLowerCase();
        if (lower.includes(wl) && !usedKeywords.has(wl)) {
            usedKeywords.add(wl);
            const val = positiveHits > trustedHits ? 5 : -5;
            score += val;
            logRisk(`Median keyword "${w}" scored ${val > 0 ? '+' : ''}${val}`, val);
        }
    }

    const SUSP = ["clone", "remove", "deepfake"];
    for (let term of SUSP) {
        if (lower.includes(term) && !usedKeywords.has(term)) {
            usedKeywords.add(term);
            score += 50;
            logRisk(`Highly suspicious term found: "${term}"`, +50);
        }
    }

    return score;
}

function parsePDFDate(s) {
    if (!s || !s.startsWith("D:")) return null;
    return new Date(`${s.slice(2,6)}-${s.slice(6,8)}-${s.slice(8,10)}T${s.slice(10,12) || "00"}:${s.slice(12,14) || "00"}:00`);
}

// ========== EVENT LISTENERS ==========
fileInput.addEventListener('change', handleFileSelect);
analyzeBtn.addEventListener('click', handleUrlAnalyze);
fileUrlInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') handleUrlAnalyze();
});
fileUrlInput.addEventListener('input', () => {
    updateFileTypeBadge(fileUrlInput.value);
});

// Example buttons
document.querySelectorAll('.example-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        fileUrlInput.value = btn.dataset.url;
        updateFileTypeBadge(btn.dataset.url);
        handleUrlAnalyze();
    });
});

// ========== URL HANDLER ==========
async function handleUrlAnalyze() {
    const urlParams = new URLSearchParams(window.location.search);
    const url = urlParams.get('file');
   

    hideError();
    riskLog = [];
    setLoading(true);

    verdictBox.textContent = 'ANALYZING...';
    verdictBox.className = 'verdict-box';
    resultsDiv.innerHTML = '';
    extraDebug.textContent = '';

    const metadata = {};
    const usedKeywords = new Set();

    try {
        const fileType = getFileType(url);
        
        if (fileType === 'pdf') {
            await processPDFFromUrl(url, metadata, usedKeywords);
        } else if (fileType === 'image') {
            await processImageFromUrl(url, metadata, usedKeywords);
        } else {
            // Try as image first
            await processImageFromUrl(url, metadata, usedKeywords);
        }
    } catch (err) {
        console.error(err);
        showError('Error analyzing file: ' + (err.message || err) + '. Make sure the URL is accessible and CORS-enabled.');
        verdictBox.textContent = 'ERROR';
        verdictBox.className = 'verdict-box risk-high';
        setTimeout(() => {
        window.close();
    }, 100); 
    } finally {
        setLoading(false);
    }
}

handleUrlAnalyze()
// ========== FILE HANDLER (local file) ==========
async function handleFileSelect(e) {
    riskLog = [];
    const file = e.target.files[0];
    if (!file) return;

    hideError();
    setLoading(true);
    
    verdictBox.textContent = 'ANALYZING...';
    verdictBox.className = 'verdict-box';
    resultsDiv.innerHTML = '';
    extraDebug.textContent = '';

    const metadata = {};
    const usedKeywords = new Set();
    try {
        let baseRisk = 0;
        if (file.type === 'application/pdf') {
            baseRisk = await processPDF(file, metadata, usedKeywords);
        } else if (file.type.startsWith('image/')) {
            baseRisk = await processImage(file, metadata, usedKeywords);
        } else {
            showError('Unsupported file type');
            return;
        }
    } catch (err) {
        console.error(err);
        showError('Error analyzing file: ' + (err.message || err));
    } finally {
        setLoading(false);
    }
}

// ========== PDF FROM URL ==========
async function processPDFFromUrl(url, metadata, usedKeywords) {
    let risk = 0;

    // Fetch PDF as ArrayBuffer
    const response = await fetch(url);
    if (!response.ok) throw new Error(`Failed to fetch PDF: ${response.status}`);
    const buffer = await response.arrayBuffer();

    const slice = buffer.slice(Math.max(0, buffer.byteLength - 150000));
    const textData = new TextDecoder().decode(slice || new ArrayBuffer(0));

    const eofCount = (textData.match(/%%EOF/g) || []).length;
    metadata["EOF Count"] = eofCount;
    metadata["Source URL"] = url;

    if (eofCount > 1) {
        const add = eofCount * 20;
        risk += add;
        logRisk(`PDF has ${eofCount} EOF markers (possible appended PDF)`, add);
    }

    const pdf = await pdfjsLib.getDocument({ data: buffer }).promise;
    const meta = await pdf.getMetadata();

    Object.entries(meta.info || {}).forEach(([key, value]) => {
        metadata[key] = value;
        risk += keywordScore(key, value, usedKeywords);
    });

    if (Object.keys(metadata).length <= 2) {
        risk += 30;
        logRisk("No PDF metadata found (blank metadata)", +30);
    }

    const c = parsePDFDate(meta.info?.CreationDate);
    const m = parsePDFDate(meta.info?.ModDate);
    if (c && m) {
        const hrs = Math.abs(m - c) / 36e5;
        if (hrs > 1) {
            metadata["Time Difference"] = hrs.toFixed(1) + " hours";
            risk += 15;
            logRisk(`Creation vs Modification time difference (${hrs.toFixed(1)} hrs)`, +15);
        }
    }

    // Render first page as image for ELA
    const page = await pdf.getPage(1);
    const viewport = page.getViewport({ scale: 1 });
    canvasOrig.width = viewport.width;
    canvasOrig.height = viewport.height;
    canvasELA.width = viewport.width;
    canvasELA.height = viewport.height;

    await page.render({ canvasContext: canvasOrig.getContext("2d"), viewport }).promise;

    return performELA(canvasOrig, canvasELA, risk, metadata);
}

// ========== IMAGE FROM URL ==========
function processImageFromUrl(url, metadata, usedKeywords) {
    return new Promise((resolve, reject) => {
        let risk = 0;
        metadata["Source URL"] = url;

        const img = new Image();
        img.crossOrigin = "anonymous";
        
        img.onerror = () => {
            reject(new Error('Failed to load image. The URL may be inaccessible or blocked by CORS policy.'));
        };

        img.onload = () => {
            // For URL-based images, we can't extract EXIF easily without fetching raw bytes
            // Add a note about this limitation
            metadata["EXIF Status"] = "Not available for remote URLs (requires raw file access)";
            
            if (Object.keys(metadata).length <= 2) {
                risk -= 20;
                logRisk("No metadata detected (URL-based) → small deduction", -20);
            }

            const maxDim = 1200;
            let w = img.width, h = img.height;
            if (w > maxDim || h > maxDim) {
                const r = Math.min(maxDim / w, maxDim / h);
                w = Math.round(w * r); h = Math.round(h * r);
            }

            canvasOrig.width = w; canvasOrig.height = h;
            canvasELA.width = w; canvasELA.height = h;
            canvasOrig.getContext('2d').drawImage(img, 0, 0, w, h);

            performELA(canvasOrig, canvasELA, risk, metadata).then(resolve).catch(reject);
        };

        img.src = url;
    });
}

// ========== PDF PROCESSING (local file) ==========
async function processPDF(file, metadata, usedKeywords) {
    let risk = 0;

    const buffer = await file.arrayBuffer();
    const slice = buffer.slice(buffer.byteLength - 150000);
    const textData = new TextDecoder().decode(slice || new ArrayBuffer(0));

    const eofCount = (textData.match(/%%EOF/g) || []).length;
    metadata["EOF Count"] = eofCount;
    metadata["File Name"] = file.name;

    if (eofCount > 1) {
        const add = eofCount * 20;
        risk += add;
        logRisk(`PDF has ${eofCount} EOF markers (possible appended PDF)`, add);
    }

    const pdf = await pdfjsLib.getDocument({ data: buffer }).promise;
    const meta = await pdf.getMetadata();

    Object.entries(meta.info || {}).forEach(([key, value]) => {
        metadata[key] = value;
        risk += keywordScore(key, value, usedKeywords);
    });

    if (Object.keys(metadata).length <= 2) {
        risk += 30;
        logRisk("No PDF metadata found (blank metadata)", +30);
    }

    const c = parsePDFDate(meta.info?.CreationDate);
    const m = parsePDFDate(meta.info?.ModDate);
    if (c && m) {
        const hrs = Math.abs(m - c) / 36e5;
        if (hrs > 1) {
            metadata["Time Difference"] = hrs.toFixed(1) + " hours";
            risk += 15;
            logRisk(`Creation vs Modification time difference (${hrs.toFixed(1)} hrs)`, +15);
        }
    }

    // Render first page as image for ELA
    const page = await pdf.getPage(1);
    const viewport = page.getViewport({ scale: 1 });
    canvasOrig.width = viewport.width;
    canvasOrig.height = viewport.height;
    canvasELA.width = viewport.width;
    canvasELA.height = viewport.height;

    await page.render({ canvasContext: canvasOrig.getContext("2d"), viewport }).promise;

    return performELA(canvasOrig, canvasELA, risk, metadata);
}

// ========== IMAGE PROCESSING (local file) ==========
function processImage(file, metadata, usedKeywords) {
    return new Promise(resolve => {
        let risk = 0;
        metadata["File Name"] = file.name;
        
        EXIF.getData(file, function () {
            const tags = EXIF.getAllTags(this) || {};
            Object.entries(tags).forEach(([key, value]) => {
                metadata[key] = String(value);
                const kScore = keywordScore(key, value, usedKeywords);
                risk += kScore;
            });

            if (tags.DateTimeOriginal && tags.DateTimeDigitized && tags.DateTimeOriginal !== tags.DateTimeDigitized) {
                metadata["Timestamp Mismatch"] = "Original vs Digitized mismatch";
                risk += 20;
                logRisk("EXIF DateTime mismatch (Original vs Digitized)", +20);
            }

            if (Object.keys(metadata).length <= 1) {
                risk -= 20;
                logRisk("No metadata detected → small deduction", -20);
            }

            const img = new Image();
            img.onload = () => {
                const maxDim = 1200;
                let w = img.width, h = img.height;
                if (w > maxDim || h > maxDim) {
                    const r = Math.min(maxDim / w, maxDim / h);
                    w = Math.round(w * r); h = Math.round(h * r);
                }

                canvasOrig.width = w; canvasOrig.height = h;
                canvasELA.width = w; canvasELA.height = h;
                canvasOrig.getContext('2d').drawImage(img, 0, 0, w, h);

                resolve(performELA(canvasOrig, canvasELA, risk, metadata));
            };
            img.src = URL.createObjectURL(file);
        });
    });
}

// ========== IMPROVED ELA (multi-pass + clustering) ==========
function performELA(src, out, baseRisk, metadata) {
    const w = src.width, h = src.height;
    const ctxS = src.getContext('2d');
    const ctxO = out.getContext('2d');

    const orig = ctxS.getImageData(0, 0, w, h);
    const jpeg = src.toDataURL('image/jpeg', 0.92);
    const img = new Image();

    return new Promise(resolve => {
        img.onload = () => {
            // draw compressed image to temp canvas (ctxO)
            ctxO.clearRect(0,0,w,h);
            ctxO.drawImage(img, 0, 0, w, h);
            const comp = ctxO.getImageData(0, 0, w, h);
            const outData = ctxO.createImageData(w, h);

            // parameters
            const HIGH_THRESHOLD = 120;
            const MED_THRESHOLD = 40;
            const MIN_CLUSTER_SIZE = 5;
            const CLUSTER_PIXEL_FLAG = 1;

            const diffMap = new Float32Array(w * h);
            const binaryMap = new Uint8Array(w * h);
            let highCount = 0, medCount = 0;

            // compute per-pixel difference map
            for (let i = 0, p = 0; i < orig.data.length; i += 4, p++) {
                const r1 = orig.data[i], g1 = orig.data[i+1], b1 = orig.data[i+2];
                const r2 = comp.data[i], g2 = comp.data[i+1], b2 = comp.data[i+2];
                const diff = (Math.abs(r1 - r2) + Math.abs(g1 - g2) + Math.abs(b1 - b2)) / 3;
                const val = diff * 18; // amplification scale
                diffMap[p] = val;

                if (val > HIGH_THRESHOLD) {
                    highCount++;
                    if (val > MED_THRESHOLD) binaryMap[p] = CLUSTER_PIXEL_FLAG;
                } else if (val > MED_THRESHOLD) {
                    medCount++;
                    if (val > MED_THRESHOLD) binaryMap[p] = CLUSTER_PIXEL_FLAG;
                }

                // render heatmap color
                if (val < 20) {
                    outData.data[i] = 0; outData.data[i+1] = 0; outData.data[i+2] = Math.round(val);
                } else if (val < 80) {
                    outData.data[i] = 0; outData.data[i+1] = Math.round(val); outData.data[i+2] = 255 - Math.round(val);
                } else {
                    outData.data[i] = 255; outData.data[i+1] = 0; outData.data[i+2] = 0;
                }
                outData.data[i+3] = 255;
            }

            // connected components (BFS)
            const visited = new Uint8Array(w * h);
            const clusters = [];

            function bfs(sx, sy, sIdx) {
                const queue = [sIdx];
                visited[sIdx] = 1;
                const cluster = { size: 0, totalDiff: 0, minDiff: Infinity, maxDiff: 0, pixels: [], minX: sx, maxX: sx, minY: sy, maxY: sy };

                while (queue.length) {
                    const idx = queue.shift();
                    const y = Math.floor(idx / w), x = idx % w;
                    cluster.pixels.push({ x, y, idx });
                    cluster.size++;
                    const dval = diffMap[idx];
                    cluster.totalDiff += dval;
                    cluster.minDiff = Math.min(cluster.minDiff, dval);
                    cluster.maxDiff = Math.max(cluster.maxDiff, dval);
                    cluster.minX = Math.min(cluster.minX, x);
                    cluster.maxX = Math.max(cluster.maxX, x);
                    cluster.minY = Math.min(cluster.minY, y);
                    cluster.maxY = Math.max(cluster.maxY, y);

                    // neighbors
                    const dirs = [[1,0],[-1,0],[0,1],[0,-1]];
                    for (let [dx,dy] of dirs) {
                        const nx = x + dx, ny = y + dy;
                        if (nx >= 0 && nx < w && ny >= 0 && ny < h) {
                            const nidx = ny * w + nx;
                            if (!visited[nidx] && binaryMap[nidx] === CLUSTER_PIXEL_FLAG) {
                                visited[nidx] = 1;
                                queue.push(nidx);
                            }
                        }
                    }
                }

                cluster.avgDiff = cluster.totalDiff / cluster.size;
                cluster.width = cluster.maxX - cluster.minX + 1;
                cluster.height = cluster.maxY - cluster.minY + 1;
                cluster.aspectRatio = cluster.width / cluster.height;
                cluster.density = cluster.size / (cluster.width * cluster.height);
                return cluster;
            }

            for (let y = 0; y < h; y++) {
                for (let x = 0; x < w; x++) {
                    const idx = y * w + x;
                    if (binaryMap[idx] === CLUSTER_PIXEL_FLAG && !visited[idx]) {
                        const cluster = bfs(x, y, idx);
                        if (cluster.size >= MIN_CLUSTER_SIZE) clusters.push(cluster);
                    }
                }
            }

            ctxO.putImageData(outData, 0, 0);

            // === STATISTICS & RISK SCORING ===
            const pixelCount = w * h;
            const highRatio = highCount / pixelCount;
            const medRatio = medCount / pixelCount;

            // cluster classification
            const highDiffClusters = clusters.filter(c => c.avgDiff > HIGH_THRESHOLD);
            const medDiffClusters  = clusters.filter(c => c.avgDiff > MED_THRESHOLD && c.avgDiff <= HIGH_THRESHOLD);

            // 1) cluster-based scoring (max 70)
            let clusterRisk = 0;
            for (const c of highDiffClusters) {
                if (c.size > 50 && c.density > 0.7) {
                    clusterRisk += 10; logRisk('ELA: Large dense high-error cluster', +10);
                } else if (c.size > 20) {
                    clusterRisk += 7; logRisk('ELA: High-error cluster (size > 20)', +7);
                } else {
                    clusterRisk += 3; logRisk('ELA: Small high-error cluster', +3);
                }

                if (c.aspectRatio > 3 || c.aspectRatio < 0.33) {
                    clusterRisk += 5; logRisk('ELA: Irregularly shaped cluster', +5);
                }
            }

            clusterRisk = Math.min(70, clusterRisk);

            // 2) global statistics
            let globalRisk = 0;
            if (highRatio > 0.005) { globalRisk += 5; logRisk('ELA: Notable high-diff pixel ratio', +5); }
            if (medRatio > 0.12) { globalRisk += 5; logRisk('ELA: Widespread medium-diff pattern', +5); }

            globalRisk = Math.min(50, globalRisk);

            // 3) cluster pattern risk
            let patternRisk = 0;
            if (clusters.length > 10 && clusters.length < 50) { patternRisk += 5; logRisk(`ELA: Multiple clusters (${clusters.length})`, +5); }
            if (clusters.length >= 50) { patternRisk += 10; logRisk(`ELA: Very large number of clusters (${clusters.length})`, +10); }
            patternRisk = Math.min(20, patternRisk);

            // Compose ELA risk
            let elaRisk = clusterRisk + globalRisk + patternRisk;
            // intentionally cap per policy: small final ELA addition so metadata still matters
            elaRisk = Math.min(30, elaRisk);

            // Compose final risk and clamp
            const totalRisk = baseRisk + elaRisk;
            const finalRisk = Math.min(100, Math.max(0, totalRisk));

            // Optional: draw bounding boxes for first N clusters
            const overlayCtx = out.getContext('2d');
            overlayCtx.strokeStyle = "rgba(0,255,0,0.6)";
            overlayCtx.lineWidth = 1.5;
            highDiffClusters.slice(0, 10).forEach(c => {
                if (c.size > 10) {
                    overlayCtx.strokeRect(c.minX, c.minY, c.width, c.height);
                }
            });

            // Prepare structured data for UI
            const elaStats = {
                highPct: (highRatio * 100).toFixed(3),
                midPct: (medRatio * 100).toFixed(3),
                totalClusters: clusters.length,
                highClusters: highDiffClusters.length,
                midClusters: medDiffClusters.length
            };

            const clusterSummaries = clusters.slice(0, 20).map(c => ({
                pixels: c.size,
                avgDiff: Math.round(c.avgDiff),
                density: c.density.toFixed(3),
                aspect: c.aspectRatio.toFixed(2),
                risk: Math.round(Math.min(15, (c.size > 50 ? 10 : c.size > 20 ? 6 : 2) + (c.aspectRatio > 3 || c.aspectRatio < 0.33 ? 5 : 0)))
            }));

            // call UI renderer
            renderResults(metadata, riskLog.slice(), elaStats, clusterSummaries, {
                baseRisk: baseRisk,
                elaRisk: elaRisk,
                finalRisk: finalRisk,
                clusterRisk, globalRisk, patternRisk
            });

            // debug short info
            extraDebug.textContent = `Pixels: ${pixelCount}, Clusters: ${clusters.length}`;

            resolve(finalRisk);
        };

        img.src = jpeg;
    });
}

// ========== CLEAN RESULT DISPLAY ==========
function renderResults(metadata, keywordLog, elaStats, elaClusters, finalRiskObj) {
    // update quick summary
    const score = finalRiskObj.finalRisk;
    riskScoreDisplay.textContent = `Risk Score: ${score}/100`;
    elaScoreDisplay.textContent = `ELA Risk: ${finalRiskObj.elaRisk}`;

    verdictBox.className = 'verdict-box';
    if (score <= 30) {
        verdictBox.textContent = "LIKELY AUTHENTIC";
        verdictBox.classList.add('risk-low');
    } else if (score < 65) {
        verdictBox.textContent = "SUSPICIOUS";
        verdictBox.classList.add('risk-med');
    } else {
        verdictBox.textContent = "POTENTIAL TAMPERING";
        verdictBox.classList.add('risk-high');
    }

    // Build metadata panel
    const metaHtml = renderMetadataTable(metadata);
    const keywordHtml = renderKeywordLog(keywordLog);
    const elaStatsHtml = renderELAStats(elaStats);
    const elaClustersHtml = renderELAClusters(elaClusters);
    const finalHtml = renderFinalRiskPanel(finalRiskObj);

    resultsDiv.innerHTML = `
        <div class="mb-3">${metaHtml}</div>
        <div class="mb-3">
            <div class="card">
                <div class="card-header fw-bold">Keyword & Metadata Scan</div>
                <div class="card-body p-2">${keywordHtml}</div>
            </div>
        </div>
        <div class="mb-3">
            <div class="card">
                <div class="card-header fw-bold">ELA Analysis</div>
                <div class="card-body p-2">
                    ${elaStatsHtml}
                    ${elaClustersHtml}
                </div>
            </div>
        </div>
        <div class="mb-3">${finalHtml}</div>
        <div class="small-muted"><strong>Audit Log (raw):</strong></div>
        <pre class="json" style="overflow-x:hidden">${escapeHtml(JSON.stringify(keywordLog, null, 2))}</pre>
    `;

    // clear riskLog for next file
    riskLog = [];
}

function renderMetadataTable(metadata) {
    if (!metadata || Object.keys(metadata).length === 0) {
        return `<div class="card"><div class="card-body"><em>No metadata found.</em></div></div>`;
    }
    const rows = Object.entries(metadata).map(([k,v]) => `
        <tr>
            <td class="fw-semibold" style="width:40%">${escapeHtml(k)}</td>
            <td>${escapeHtml(String(v))}</td>
        </tr>
    `).join('');
    return `
        <div class="card">
            <div class="card-header fw-bold">Metadata</div>
            <div class="card-body p-2">
                <table class="table table-sm table-bordered mb-0">
                    <tbody>${rows}</tbody>
                </table>
            </div>
        </div>
    `;
}

function renderKeywordLog(logs) {
    if (!logs || logs.length === 0) return `<div class="p-2"><em>No keyword matches detected.</em></div>`;
    return logs.map(i => `
        <div class="d-flex justify-content-between border-bottom py-1">
            <div class="small">${escapeHtml(i.reason)}</div>
            <div class="${i.value >= 0 ? 'text-danger' : 'text-success'} fw-bold">
                ${i.value >= 0 ? '+' : ''}${i.value}
            </div>
        </div>
    `).join('');
}

function renderELAStats(stats) {
    return `
        <div class="mb-2">
            <table class="table table-sm table-bordered mb-2">
                <tbody>
                    <tr><td>High-Diff Pixels</td><td>${stats.highPct}%</td></tr>
                    <tr><td>Medium-Diff Pixels</td><td>${stats.midPct}%</td></tr>
                    <tr><td>Total Clusters</td><td>${stats.totalClusters}</td></tr>
                    <tr><td>High-Error Clusters</td><td>${stats.highClusters}</td></tr>
                    <tr><td>Medium-Error Clusters</td><td>${stats.midClusters}</td></tr>
                </tbody>
            </table>
        </div>
    `;
}

function renderELAClusters(clusters) {
    if (!clusters || clusters.length === 0) return `<div class="p-2"><em>No clusters detected.</em></div>`;
    return `
        <div>
            ${clusters.map((c, i) => `
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <div><strong>Cluster ${i+1}</strong> — ${c.pixels} pixels</div>
                        <div class="${c.risk > 0 ? 'text-danger' : 'text-success'} fw-bold">Risk: ${c.risk>0?'+':''}${c.risk}</div>
                    </div>
                    <div class="small-muted mt-1">
                        Avg Diff: ${c.avgDiff} • Density: ${c.density} • Aspect: ${c.aspect}
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function renderFinalRiskPanel(finalObj) {
    const base = finalObj.baseRisk || 0, ela = finalObj.elaRisk || 0, total = finalObj.finalRisk;
    return `
        <div class="card">
            <div class="card-header fw-bold">Final Risk Summary</div>
            <div class="card-body p-2">
                <table class="table table-sm mb-2">
                    <tbody>
                        <tr><td>Base Metadata/Keyword Risk</td><td>+${base}</td></tr>
                        <tr><td>ELA Risk</td><td>+${ela}</td></tr>
                        <tr><td><strong>Total (clamped)</strong></td><td><strong>${total}</strong></td></tr>
                    </tbody>
                </table>
                <div class="text-center">
                    <div class="display-6 fw-bold ${total >= 75 ? 'text-danger' : total >= 40 ? 'text-warning' : 'text-success'}">${total}</div>
                    <div class="fw-semibold">${total >= 75 ? 'HIGH RISK' : total >= 40 ? 'MEDIUM RISK' : 'LOW RISK'}</div>
                </div>
            </div>
        </div>
    `;
}

function escapeHtml(s) {
    return String(s)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
</script>


</body>
</html>
