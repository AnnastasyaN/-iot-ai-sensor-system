import sys
import json
import os
import joblib
import pandas as pd

# Get the directory of this script
BASE_DIR = os.path.dirname(os.path.abspath(__file__))

# Use os.path.join for cross-platform compatibility
MODEL_PATH = os.path.join(BASE_DIR, "model.pkl")
SCALER_PATH = os.path.join(BASE_DIR, "scaler.pkl")
LABEL_ENCODER_PATH = os.path.join(BASE_DIR, "label_encoder.pkl")

FEATURE_COLUMNS = [
    "temperature",
    "humidity",
    "co",
    "lpg",
    "smoke",
    "light_intensity",
    "motion_status"
]

def main():
    try:
        # Support both command line argument and file input
        if len(sys.argv) > 2 and sys.argv[1] == "--file":
            # Read JSON from file
            temp_file = sys.argv[2]
            if not os.path.exists(temp_file):
                raise ValueError(f"Temp file not found: {temp_file}")
            with open(temp_file, 'r') as f:
                input_json = f.read()
            # Clean up temp file
            try:
                os.remove(temp_file)
            except:
                pass
        elif len(sys.argv) > 1:
            # Use command line argument (for backward compatibility)
            input_json = sys.argv[1]
        else:
            raise ValueError("Input JSON tidak ditemukan.")
        
        data = json.loads(input_json)

        input_data = pd.DataFrame([{
            "temperature": float(data["temperature"]),
            "humidity": float(data["humidity"]),
            "co": float(data["co"]),
            "lpg": float(data["lpg"]),
            "smoke": float(data["smoke"]),
            "light_intensity": float(data["light_intensity"]),
            "motion_status": int(data["motion_status"])
        }], columns=FEATURE_COLUMNS)

        model = joblib.load(MODEL_PATH)
        scaler = joblib.load(SCALER_PATH)
        label_encoder = joblib.load(LABEL_ENCODER_PATH)

        input_scaled = scaler.transform(input_data)

        prediction_code = model.predict(input_scaled)[0]
        prediction_proba = model.predict_proba(input_scaled)[0]

        prediction_label = label_encoder.inverse_transform([prediction_code])[0]
        prediction_score = float(max(prediction_proba))

        result = {
            "status": "success",
            "prediction_label": prediction_label,
            "prediction_score": round(prediction_score, 4),
            "prediction_code": int(prediction_code)
        }

        print(json.dumps(result))

    except Exception as e:
        result = {
            "status": "error",
            "message": str(e)
        }

        print(json.dumps(result))


if __name__ == "__main__":
    main()
