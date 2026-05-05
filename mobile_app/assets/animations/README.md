# Simple Rive Heart Animation

## File: `heart.riv`

**Status**: Current file contains text elements that require missing native libraries. Create a new simple heart animation without text.

**Important**: Create the animation WITHOUT any text elements to avoid native library loading issues.

### Animation Requirements:

1. **State Machine**: `HeartStateMachine`
2. **Inputs**:
   - `fill_percentage` (Number, 0-1): Controls blood fill level (0 = empty, 1 = full)
   - `beat` (Trigger): Triggers heartbeat animation on score increase

## Simple Heart Animation Tutorial

### Step 1: Create New Rive File
1. Go to [rive.app](https://rive.app) and create a new file
2. Set artboard size to **200x200px** (square for heart)
3. Set background to transparent
4. **Important**: Do NOT add any text elements - they cause loading issues

### Step 2: Draw the Heart Shape
1. Create a **Path** component
2. Draw a simple heart shape using the pen tool:
   - Start at top center
   - Create two curved bumps for the top
   - Add a sharp point at the bottom
3. Fill with deep red color (#DC143C)

### Step 3: Create Blood Fill Effect
1. Create a **Shape** component for the blood (same heart shape)
2. Fill with darker red (#B22222)
3. Add a **Trim Path** to control fill level
4. Connect `fill_percentage` input to Trim Path → End

### Step 4: Add Heartbeat Animation
1. Create a **Scale** animation on the heart shape
2. Keyframe: Normal size → Slightly larger → Back to normal
3. Duration: ~0.3 seconds with bounce easing
4. Trigger this animation when `beat` input is fired

### Step 5: Breathing Animation
1. Create subtle scale animation (idle state)
2. Very small scale change (1.0 → 1.02 → 1.0)
3. Loop continuously with ease in/out

### Step 6: State Machine Setup
1. Add **State Machine** component
2. Create states: `Idle`, `Beating`
3. Add inputs:
   - `fill_percentage` (Number, 0-1)
   - `beat` (Trigger)
4. Connect `beat` trigger to transition to `Beating` state
5. Auto-transition back to `Idle` after beat animation

### Step 7: Export
1. Export as `heart.riv`
2. Place in `mobile_app/assets/animations/` directory
3. Test in Flutter app

### Visual Features:
- **Heart Shape**: Clean, recognizable heart silhouette
- **Blood Fill**: Progressive red fill from bottom to top
- **Heartbeat**: Satisfying bounce animation
- **Breathing**: Subtle pulsing when at rest
- **Colors**: Deep red (#DC143C) for heart, darker red (#B22222) for blood

### Technical Specs:
- **Artboard**: 200x200px
- **Frame Rate**: 60fps (default)
- **State Machine**: `HeartStateMachine`
- **Inputs**: `fill_percentage` (Number), `beat` (Trigger)

### Fallback:
If Rive file is missing, the app uses enhanced Flutter animations with realistic painter-based heart.

### Quick Test:
Once created, the heart should:
- Start with subtle breathing animation
- Fill with red color based on `fill_percentage` (0-100%)
- Bounce/beat when `beat` trigger is fired
- Look smooth and satisfying

### Ultra-Simple Version (Guaranteed to Work):
1. Create a red circle (ellipse shape)
2. Add state machine with `fill_percentage` and `beat` inputs
3. Make circle opacity = fill_percentage
4. Scale animation for beat: 1.0 → 1.3 → 1.0
5. Export as `heart.riv`

### Troubleshooting:
- If Rive animation fails to load, the app automatically falls back to custom Flutter animations
- Make sure your Rive file contains NO text elements
- Test the animation in Rive editor first before exporting
- If issues persist, the custom heart animation will work perfectly
