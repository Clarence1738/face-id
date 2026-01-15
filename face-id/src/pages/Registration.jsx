import React, { useRef, useState, useEffect } from 'react';
import * as faceapi from 'face-api.js';
import { CheckCircle2, XCircle, AlertTriangle, AlertCircle, Search } from 'lucide-react';
import '../styles/Registration.css';

export default function Register() {
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [status, setStatus] = useState('Initializing...');
  const [statusIcon, setStatusIcon] = useState(null);
  const [isScanning, setIsScanning] = useState(false);
  const videoRef = useRef(null);
  const canvasRef = useRef(null);

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
      setStatus('Please fill in your details before capturing an image.');
      setStatusIcon(<AlertCircle size={20} />);
      return;
    }

    setStatus('Processing face...');
    setStatusIcon(<Search size={20} />);
    setIsScanning(true);

    // Draw the video frame to the canvas
    const canvas = faceapi.createCanvasFromMedia(videoRef.current);
    canvasRef.current = canvas;

    // Detect face and create a face descriptor
    const detection = await faceapi
      .detectSingleFace(videoRef.current, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor();

    if (!detection) {
      setIsScanning(false);
      setStatus('No face detected. Try again.');
      setStatusIcon(<XCircle size={20} />);
      return;
    }

    const payload = {
      name,
      phone,
      descriptor: Array.from(detection.descriptor),
    };

    try {
      const response = await fetch('http://localhost/face-id/backend/index.php/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      if (response.ok) {
        setStatus('Registered successfully!');
        setStatusIcon(<CheckCircle2 size={20} />);
        setName('');
        setPhone('');
      } else {
        const result = await response.json();
        setStatus(result.message || 'Error during registration.');
        setStatusIcon(<XCircle size={20} />);
      }
    } catch (error) {
      console.error('Error:', error);
      setStatus('Failed to send data to server.');
      setStatusIcon(<AlertTriangle size={20} />);
    } finally {
      setIsScanning(false);
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

        <div className="video-wrapper">
          <video ref={videoRef} width="400" height="300" autoPlay muted />
          <div className={`scan-overlay ${isScanning ? 'scanning' : ''}`}>
            <div className="corner corner-tl"></div>
            <div className="corner corner-tr"></div>
            <div className="corner corner-bl"></div>
            <div className="corner corner-br"></div>
            <div className="tracking-points">
              {[...Array(10)].map((_, i) => <div key={i} className="tracking-point"></div>)}
            </div>
            <div className="mesh-lines">
              <svg viewBox="0 0 100 100" preserveAspectRatio="none">
                <line x1="35" y1="25" x2="65" y2="25" />
                <line x1="35" y1="25" x2="50" y2="35" />
                <line x1="65" y1="25" x2="50" y2="35" />
                <line x1="30" y1="50" x2="50" y2="35" />
                <line x1="70" y1="50" x2="50" y2="35" />
                <line x1="30" y1="50" x2="50" y2="60" />
                <line x1="70" y1="50" x2="50" y2="60" />
                <line x1="40" y1="70" x2="50" y2="60" />
                <line x1="60" y1="70" x2="50" y2="60" />
                <line x1="40" y1="70" x2="60" y2="70" />
              </svg>
            </div>
          </div>
        </div>

        <button onClick={handleCapture} className="button">
          Capture and Register
        </button>

        <p className="status">
          {statusIcon && <span className="status-icon">{statusIcon}</span>}
          {status}
        </p>
      </div>
    </div>
  );
}
