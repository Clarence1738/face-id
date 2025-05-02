import React, { useRef, useState, useEffect } from 'react';
import * as faceapi from 'face-api.js';
import './Recognition.css';

export default function Recognize() {
  const videoRef = useRef(null);
  const [status, setStatus] = useState("Initializing...");
  const [user, setUser] = useState(null); // State to store matched user info
  const [isCheckedIn, setIsCheckedIn] = useState(false); // State to track if user has checked in

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

    const detection = await faceapi
      .detectSingleFace(videoRef.current, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor();

    if (!detection) {
      setStatus("No face detected. Please try again.");
      return;
    }

    const payload = {
      descriptor: Array.from(detection.descriptor)
    };

    try {
      const response = await fetch("http://localhost/backend/recognize.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });

      const result = await response.json();
      if (response.ok && result.match) {
        setUser(result.user); // Set the matched user
        setStatus(`✅ Match found: ${result.user.name} (${result.user.phone})`);

        // Check if the user is already checked in
        checkIfCheckedIn(result.user.id);
      } else {
        setStatus("❌ No match found.");
      }
    } catch (error) {
      console.error("Recognition error:", error);
      setStatus("⚠️ Error contacting server.");
    }
  };

  const checkIfCheckedIn = async (userId, userName) => {
    try {
      const response = await fetch("http://localhost/backend/checkin-status.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ user_id: userId })
      });
  
      const result = await response.json();
      if (response.ok && result.checkedIn) {
        setIsCheckedIn(true);
        setStatus(`${userName} is already checked in.`);
      } else {
        setIsCheckedIn(false); // User is not checked in yet
      }
    } catch (error) {
      console.error("Error checking check-in status:", error);
      setStatus("⚠️ Error contacting server for check-in status.");
    }
  };
  

  const handleCheckIn = async () => {
    if (!user) {
      setStatus("❌ No user recognized to check in.");
      return;
    }

    // Check if user is already checked in
    if (isCheckedIn) {
      setStatus(`${user.name} is already checked in.`);
      return;
    }

    try {
      const response = await fetch("http://localhost/backend/checkin.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ user_id: user.id })
      });

      const result = await response.json();
      if (response.ok) {
        setStatus(`✅ ${user.name} checked in at ${new Date().toLocaleTimeString()}`);
        setIsCheckedIn(true); // Update check-in status
      } else {
        setStatus("❌ Failed to check in.");
      }
    } catch (error) {
      console.error("Check-in error:", error);
      setStatus("⚠️ Error contacting server for check-in.");
    }
  };

  return (
    <div className="recognition-container">
      <h2>Face Recognition</h2>
      <video ref={videoRef} autoPlay muted width="320" height="240" />
      <button onClick={handleRecognize}>Recognize</button>
      {user && !isCheckedIn && <button onClick={handleCheckIn}>Check In</button>} {/* Only show Check In button if not checked in */}
      {isCheckedIn && <p>{user.name} has already checked in!</p>} {/* Display checked-in message */}
      <p>{status}</p>
    </div>
  );
}
