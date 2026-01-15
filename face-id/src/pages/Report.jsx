import React, { useEffect, useState } from 'react';
import '../styles/Report.css';

export default function Report() {
  const [checkins, setCheckins] = useState([]);

  useEffect(() => {
    fetch("http://localhost/face-id/backend/index.php/report")
      .then(res => res.json())
      .then(data => setCheckins(data))
      .catch(err => {
        console.error("Error fetching report:", err);
        setCheckins([]);
      });
  }, []);

  return (
    <div className="report-container">
      <h2>Check-In Report</h2>
      {checkins.length === 0 ? (
        <p>No check-ins yet.</p>
      ) : (
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Phone</th>
              <th>Check-in Time</th>
              <th>Accuracy</th>
              <th>Device IP</th>
            </tr>
          </thead>
          <tbody>
            {checkins.map((item, index) => (
              <tr key={index}>
                <td>{item.name}</td>
                <td>{item.phone}</td>
                <td>{new Date(item.checkin_time).toLocaleString()}</td>
                <td>{(item.confidence * 100).toFixed(2)}%</td>
                <td>{item.device_ip}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}
