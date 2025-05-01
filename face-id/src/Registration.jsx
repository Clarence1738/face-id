import React, { useRef, useState, useEffect } from 'react';
import * as faceapi from 'face-api.js';
import './Registration.css';

export default function Register() {
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [image, setImage] = useState(null);
  const [status, setStatus] = useState('Initializing...');
  const imageRef = useRef(null);

  useEffect(() => {
    const MODEL_URL = '/models';
    Promise.all([
      faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
      faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
      faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
    ]).then(() => {
      console.log("Models loaded");
    });
  }, []);
  

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

  const handleRegister = async () => {
    if (!image || !name || !phone) {
      setStatus("❗ Please complete all fields and upload a photo.");
      return;
    }

    setStatus("🔍 Processing face...");

    const detection = await faceapi
      .detectSingleFace(image, new faceapi.TinyFaceDetectorOptions()) // ✅ Explicitly pass TinyFaceDetectorOptions
      .withFaceLandmarks()
      .withFaceDescriptor();

    if (!detection) {
      setStatus("❌ No face detected. Try another image.");
      return;
    }

    const payload = {
      name,
      phone,
      descriptor: Array.from(detection.descriptor)
    };

    try {
      const response = await fetch("http://localhost/backend/register.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });

      if (response.ok) {
        setStatus("✅ Registered successfully!");
        setName('');
        setPhone('');
        setImage(null);
      } else {
        setStatus("❌ Server error during registration.");
      }
    } catch (error) {
      console.error("Error:", error);
      setStatus("⚠️ Failed to send data to server.");
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
          onChange={e => setName(e.target.value)}
        />

        <input
          type="text"
          className="input"
          placeholder="Enter phone number"
          value={phone}
          onChange={e => setPhone(e.target.value)}
        />

        <input
          type="file"
          accept="image/*"
          className="input"
          onChange={handleImageUpload}
        />

        {image && (
          <div className="preview-container">
            <img
              ref={imageRef}
              src={image.src}
              alt="Uploaded Preview"
              className="captured-image"
            />
          </div>
        )}

        <button onClick={handleRegister} className="button">
          Submit
        </button>

        <p className="status">{status}</p>
      </div>
    </div>
  );
}
