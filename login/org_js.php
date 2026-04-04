//pram.js code
function getenimdata(rawimdata) {
    // Fixed pattern that both JS and PHP know about
    const SALT_PATTERN = 'X7mK9pQ3';
    const SALT_INTERVAL = 16; // Insert salt every 16 characters
    
    let imdata = '';
    for (let i = 0; i < rawimdata.length; i += SALT_INTERVAL) {
        imdata += rawimdata.substring(i, i + SALT_INTERVAL);
        if (i + SALT_INTERVAL < rawimdata.length) {
            imdata += SALT_PATTERN;
        }
    }
    
    return imdata;
}


//fl503 code
// Enhanced Professional Tab Functionality
const tabs = document.querySelectorAll('.tab-link');
const tabContents = document.querySelectorAll('.tab-pane');

tabs.forEach(tab => {
    tab.addEventListener('click', event => {
        event.preventDefault();
        
        tabs.forEach(t => t.classList.remove('active'));
        tabContents.forEach(tc => tc.classList.remove('show', 'active'));
        
        tab.classList.add('active');
        
        const target = document.querySelector(tab.getAttribute('href'));
        if (target) {
            target.classList.add('show', 'active');
        }
    });
});

const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const captureAndLoginButton = document.getElementById('captureAndLogin');
const usernameInput = document.getElementById('username1');
const messageDiv = document.getElementById('message1');
const loader = document.getElementById('loader');
const cameraStatus = document.getElementById('cameraStatus');

let stream = null;
let isProcessing = false;

async function initializeCamera() {
    try {
        cameraStatus.className = 'camera-status-bar';
        cameraStatus.innerHTML = '<div class="status-indicator"></div><span class="status-text">Initializing camera...</span>';
        
        const constraints = {
            video: {
                width: { ideal: 1280, min: 640 },
                height: { ideal: 720, min: 480 },
                facingMode: "user",
                frameRate: { ideal: 30, min: 15 }
            }
        };
        
        stream = await navigator.mediaDevices.getUserMedia(constraints);
        video.srcObject = stream;
        
        video.addEventListener('loadedmetadata', () => {
            console.log(`Camera initialized: ${video.videoWidth}x${video.videoHeight}`);
            
            canvas.width = 280;
            canvas.height = 420;
            
            cameraStatus.className = 'camera-status-bar ready';
            cameraStatus.innerHTML = '<div class="status-indicator"></div><span class="status-text">Camera Ready</span><span class="status-details">Position your face within the round frame</span>';
        });
        
    } catch (error) {
        console.error('Camera initialization failed:', error);
        cameraStatus.className = 'camera-status-bar error';
        cameraStatus.innerHTML = '<div class="status-indicator"></div><span class="status-text">Camera Error</span><span class="status-details">Please allow camera access</span>';
    }
}

function getidata() {
    const context = canvas.getContext('2d');
    
    context.imageSmoothingEnabled = true;
    context.imageSmoothingQuality = 'high';
    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, canvas.width, canvas.height);
    
    const videoAspectRatio = video.videoWidth / video.videoHeight;
    const targetAspectRatio = canvas.width / canvas.height;
    
    let sourceX, sourceY, sourceWidth, sourceHeight;
    
    if (videoAspectRatio > targetAspectRatio) {
        sourceHeight = video.videoHeight;
        sourceWidth = sourceHeight * targetAspectRatio;
        sourceX = (video.videoWidth - sourceWidth) / 2;
        sourceY = 0;
    } else {
        sourceWidth = video.videoWidth;
        sourceHeight = sourceWidth / targetAspectRatio;
        sourceX = 0;
        sourceY = video.videoHeight * 0.05;
    }
    
    context.drawImage(video, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, canvas.width, canvas.height);
    
    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    const data = imageData.data;
    
    for (let i = 0; i < data.length; i += 4) {
        data[i] = Math.min(255, Math.max(0, data[i] * 1.03 + 2));
        data[i + 1] = Math.min(255, Math.max(0, data[i + 1] * 1.03 + 2));
        data[i + 2] = Math.min(255, Math.max(0, data[i + 2] * 1.03 + 2));
    }
    
    context.putImageData(imageData, 0, 0);
    
    const idata = canvas.toDataURL('image/jpeg', 0.95).split(',')[1];
    
    return getenidata(idata);
}

captureAndLoginButton.addEventListener('click', async () => {
    if (isProcessing) return;
    
    const username = usernameInput.value.trim();
    if (!username) {
        messageDiv.innerHTML = '<p style="color: #f59e0b; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2);">Please enter your username to continue</p>';
        usernameInput.focus();
        return;
    }

    if (video.readyState !== video.HAVE_ENOUGH_DATA) {
        messageDiv.innerHTML = '<p style="color: #f59e0b; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2);">Camera is initializing. Please wait a moment...</p>';
        return;
    }

    isProcessing = true;
    captureAndLoginButton.disabled = true;
    messageDiv.innerHTML = '<p style="color: #3b82f6; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2);">Capturing biometric data...</p>';
    
    try {
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        const idata = getidata();

        messageDiv.innerHTML = '<p style="color: #3b82f6; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2);">Verifying identity and authenticating...</p>';
        
        const authResponse = await fetch(M.cfg.wwwroot + '/lib/fl503.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                username: username, 
                image_data: idata
            })
        });
        
        const responseText = await authResponse.text();
        
        let authResult;
        try {
            authResult = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            throw new Error('Server returned invalid response: ' + responseText.substring(0, 200));
        }
        
        if (authResult.success) {
            messageDiv.innerHTML = `<p style="color: #10b981; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">${authResult.message}</p>`;
            
            if (authResult.redirect_url) {
                setTimeout(() => {
                    window.location.href = authResult.redirect_url;
                }, 1500);
            } else {
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } else {
            throw new Error(authResult.message || 'Authentication failed');
        }
        
    } catch (error) {
        console.error('Authentication error:', error);
        messageDiv.innerHTML = `<p style="color: #ef4444; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2);">${error.message || 'Authentication failed. Please try again.'}</p>`;
    } finally {
        isProcessing = false;
        captureAndLoginButton.disabled = false;
    }
});

initializeCamera();

window.addEventListener('beforeunload', () => {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
});