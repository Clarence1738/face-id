import React, { useRef, useState, useEffect } from 'react';
import * as faceapi from 'face-api.js';
import { CheckCircle2, XCircle, AlertTriangle } from 'lucide-react';
import '../styles/Recognition.css';

export default function Recognize() {
  const videoRef = useRef(null);
  const [status, setStatus] = useState("Initializing...");
  const [statusIcon, setStatusIcon] = useState(null);
  const [user, setUser] = useState(null); // State to store matched user info
  const [isCheckedIn, setIsCheckedIn] = useState(false); // State to track if user has checked in
  const [confidence, setConfidence] = useState(0); // Store confidence score
  const [isScanning, setIsScanning] = useState(false); // State for scanning animation

  useEffect(() => {
    const loadModelsAndCamera = async () => {
      const MODEL_URL = '/models';
      await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
      await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
      await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

      try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        if (videoRef.current) videoRef.current.srcObject = stream;
        setStatus("Camera ready.");
      } catch (error) {
        console.error("Camera error:", error);
        setStatus("Camera access denied.");
      }
    };

    loadModelsAndCamera();
  }, []);

  const handleRecognize = async () => {
    setStatus("Capturing and analyzing...");
    setIsScanning(true);

    const detection = await faceapi
      .detectSingleFace(videoRef.current, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor();

    if (!detection) {
      setIsScanning(false);
      setStatus("No face detected. Please try again.");
      return;
    }

    const payload = {
      descriptor: Array.from(detection.descriptor)
    };

    try {
      const response = await fetch("http://localhost/face-id/backend/index.php/recognize", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });

      const result = await response.json();
      if (response.ok && result.match) {
        setUser(result.user); // Set the matched user
        setConfidence(result.user.confidence || 0); // Store confidence score
        setStatus(`Match found: ${result.user.name} (${result.user.phone})`);
        setStatusIcon(<CheckCircle2 size={20} />);

        // Check if the user is already checked in
        checkIfCheckedIn(result.user.id, result.user.name);
      } else {
        setStatus("No match found.");
        setStatusIcon(<XCircle size={20} />);
      }
    } catch (error) {
      console.error("Recognition error:", error);
      setStatus("Error contacting server.");
      setStatusIcon(<AlertTriangle size={20} />);
    } finally {
      setIsScanning(false);
    }
  };

  const checkIfCheckedIn = async (userId, userName) => {
    try {
      const response = await fetch("http://localhost/face-id/backend/index.php/checkin-status", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ user_id: userId })
      });
  
      const result = await response.json();
      if (result.checked_in) {
        setIsCheckedIn(true); // User is already checked in
        setStatus(`${userName} is already checked in today.`);
        setStatusIcon(<AlertTriangle size={20} />);
      } else {
        setIsCheckedIn(false); // User is not checked in yet
      }
    } catch (error) {
      console.error("Error checking check-in status:", error);
      setStatus("Error contacting server for check-in status.");
      setStatusIcon(<AlertTriangle size={20} />);
    }
  };
  

  const handleCheckIn = async () => {
    if (!user) {
      setStatus("No user recognized to check in.");
      setStatusIcon(<XCircle size={20} />);
      return;
    }

    // Check if user is already checked in
    if (isCheckedIn) {
      setStatus(`${user.name} is already checked in.`);
      setStatusIcon(<AlertTriangle size={20} />);
      return;
    }

    try {
      const response = await fetch("http://localhost/face-id/backend/index.php/checkin", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ 
          user_id: user.id,
          confidence: confidence 
        })
      });

      const result = await response.json();
      
      if (response.ok) {
        setStatus(`✅ ${user.name} checked in at ${new Date().toLocaleTimeString()}`);
        setStatusIcon(<CheckCircle2 size={20} />);
        setIsCheckedIn(true); // Update check-in status
      } else {
        // Check if already checked in (409 Conflict or message contains "already checked in")
        if (response.status === 409 || (result.message && result.message.toLowerCase().includes('already checked in'))) {
          setStatus(`${user.name} is already checked in.`);
          setStatusIcon(<AlertTriangle size={20} />);
          setIsCheckedIn(true);
        } else {
          setStatus(`❌ Failed to check in: ${result.message || 'Unknown error'}`);
          setStatusIcon(<XCircle size={20} />);
        }
      }
    } catch (error) {
      console.error("Check-in error:", error);
      setStatus("⚠️ Error contacting server for check-in.");
      setStatusIcon(<AlertTriangle size={20} />);
    }
  };

  return (
    <div className="recognition-container">
      <h2>Face Recognition</h2>
      <div className="video-wrapper">
        <video ref={videoRef} autoPlay muted width="400" height="300" />
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
      
      <p className="status">
        {statusIcon && <span className="status-icon">{statusIcon}</span>}
        {status}
      </p>
      
      <button onClick={handleRecognize}>Recognize</button>
      {user && !isCheckedIn && <button onClick={handleCheckIn}>Check In</button>}
    </div>
  );
}
