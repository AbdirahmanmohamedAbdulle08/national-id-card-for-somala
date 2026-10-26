import sys
import face_recognition
import os
import json

def process_image(image_path):
    try:
        image = face_recognition.load_image_file(image_path)
        encodings = face_recognition.face_encodings(image)
        if len(encodings) == 0:
            return {"status": "error", "message": "No face detected"}
        return {"status": "success", "encoding": encodings[0].tolist()}
    except Exception as e:
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    image_path = sys.argv[1]
    result = process_image(image_path)
    print(json.dumps(result))
