import React, { useRef, useState, useEffect } from 'react';
import * as faceapi from 'face-api.js';
import './Recognition.css';

export default function Recognize() {
  const videoRef = useRef(null);
  const [status, setStatus] = useState("Initializing...");

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
        setStatus(`✅ Match found: ${result.user.name} (${result.user.phone})`);
      } else {
        setStatus("❌ No match found.");
      }
    } catch (error) {
      console.error("Recognition error:", error);
      setStatus("⚠️ Error contacting server.");
    }
  };

  return (
    <div className="recognition-container">
      <h2>Face Recognition</h2>
      <video ref={videoRef} autoPlay muted width="320" height="240" />
      <button onClick={handleRecognize}>Recognize</button>
      <p>{status}</p>
    </div>
  );
}
