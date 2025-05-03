import React, { useRef, useState, useEffect } from 'react';
import * as faceapi from 'face-api.js';
import './Registration.css';

export default function Register() {
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [status, setStatus] = useState('Initializing...');
  const videoRef = useRef(null);
  const canvasRef = useRef(null);
  const [image, setImage] = useState(null);

  useEffect(() => {
    const MODEL_URL = '/models';
    Promise.all([
      faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
      faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
      faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]).then(() => {
      console.log('Models loaded');
      startCamera();
    });

    return () => {
      stopCamera(); // Clean up on unmount
    };
  }, []);

  const startCamera = async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ video: true });
      videoRef.current.srcObject = stream;
      videoRef.current.onplay = () => {
        console.log('Camera started');
        setStatus('Camera ready');
      };
    } catch (error) {
      console.error('Camera error:', error);
      setStatus('Error accessing camera');
    }
  };

  const stopCamera = () => {
    const stream = videoRef.current?.srcObject;
    const tracks = stream?.getTracks();
    tracks?.forEach((track) => track.stop());
    setStatus('Camera stopped');
  };

  const handleCapture = async () => {
    if (!name || !phone) {
      setStatus('❗ Please fill in your details before capturing an image.');
      return;
    }

    setStatus('🔍 Processing face...');

    // Draw the video frame to the canvas
    const canvas = faceapi.createCanvasFromMedia(videoRef.current);
    canvasRef.current = canvas;
    document.body.append(canvas); // Optionally, append the canvas to the DOM

    // Detect face and create a face descriptor
    const detection = await faceapi
      .detectSingleFace(videoRef.current, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor();

    if (!detection) {
      setStatus('❌ No face detected. Try again.');
      return;
    }

    const payload = {
      name,
      phone,
      descriptor: Array.from(detection.descriptor),
    };

    try {
      const response = await fetch('https://face-recogntion.gatimusch.xyz/backend/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      if (response.ok) {
        setStatus('✅ Registered successfully!');
        setName('');
        setPhone('');
      } else {
        setStatus('❌ Error during registration.');
      }
    } catch (error) {
      console.error('Error:', error);
      setStatus('⚠️ Failed to send data to server.');
    }
  };

  const handleImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
      const img = new Image();
      img.onload = () => {
        setImage(img);
      };
      img.src = URL.createObjectURL(file);
    }
  };

  return (
    <div className="register-container">
      <div className="register-card">
        <h2>Register with Photo</h2>

        <input
          type="text"
          className="input"
          placeholder="Enter name"
          value={name}
          onChange={(e) => setName(e.target.value)}
        />

        <input
          type="text"
          className="input"
          placeholder="Enter phone number"
          value={phone}
          onChange={(e) => setPhone(e.target.value)}
        />

        <div>
          <video ref={videoRef} width="320" height="240" autoPlay muted />
        </div>

        <button onClick={handleCapture} className="button">
          Capture and Register
        </button>

        <input
          type="file"
          accept="image/*"
          className="input"
          onChange={handleImageUpload}
        />

        <p className="status">{status}</p>
      </div>
    </div>
  );
}
