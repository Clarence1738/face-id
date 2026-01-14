# Demo Directory

This directory is intended to contain demo materials for the Face-ID Recognition System.

## Adding a Demo GIF

To add a demo GIF that will appear in the main README:

1. **Record a demo** of your application showing:
   - User registration flow
   - Face recognition in action
   - Attendance check-in/check-out
   - Report generation

2. **Convert to GIF**:
   - Use tools like [ScreenToGif](https://www.screentogif.com/), [LICEcap](https://www.cockos.com/licecap/), or [Kap](https://getkap.co/)
   - Keep file size under 10MB for fast loading
   - Recommended dimensions: 800-1200px width
   - Optimize with [ezgif.com](https://ezgif.com/optimize) if needed

3. **Add to repository**:
   ```bash
   # Save your demo GIF with this exact filename
   cp your-demo.gif demo/demo.gif
   
   # Commit and push
   git add demo/demo.gif
   git commit -m "Add demo GIF"
   git push
   ```

4. The README will automatically display the demo at:
   ```markdown
   ![Demo](demo/demo.gif)
   ```

## Alternative: Screenshots

If you prefer static screenshots instead of a GIF:

1. Take screenshots of key features
2. Save them in this directory (e.g., `registration.png`, `recognition.png`)
3. Update the README to reference them:
   ```markdown
   ### Registration
   ![Registration](demo/registration.png)
   
   ### Recognition
   ![Recognition](demo/recognition.png)
   ```

## Tips for Great Demos

- **Keep it short**: 10-30 seconds is ideal
- **Show the value**: Focus on what makes your project special
- **Good lighting**: Ensure webcam capture is clear
- **Smooth flow**: Practice the demo before recording
- **Add captions**: Consider adding text overlays to explain steps

## Placeholder

Until you add your demo GIF, the README will show a broken image link. This is expected and won't affect functionality - just add your demo when ready!
