#!/bin/bash

# VedantBilling - Linux OCR & AI Stack Installer
# Supports Ubuntu/Debian

echo "🚀 Starting VedantBilling OCR Setup for Linux..."

# 1. Update Packages
echo "🔄 Updating package list..."
sudo apt-get update

# 2. Install Tesseract OCR
echo "📖 Installing Tesseract OCR..."
if ! command -v tesseract &> /dev/null; then
    sudo apt-get install -y tesseract-ocr tesseract-ocr-eng libtesseract-dev
    echo "✅ Tesseract installed successfully."
else
    echo "✅ Tesseract is already installed."
fi

# Verify Tesseract
tesseract --version

# 3. Install Ollama (AI Runner)
echo "🦙 Checking for Ollama..."
if ! command -v ollama &> /dev/null; then
    echo "⬇️ Installing Ollama..."
    curl -fsSL https://ollama.com/install.sh | sh
else
    echo "✅ Ollama is already installed."
fi

# 4. Pull Llama 3 Model
echo "🧠 Downloading Llama 3 Model (This may take a while)..."
ollama pull llama3

# 5. Test the Setup
echo "🧪 Testing Setup..."
RESPONSE=$(curl -s -X POST http://localhost:11434/api/generate -d '{
  "model": "llama3",
  "prompt": "Hello",
  "stream": false
}')

if [[ $RESPONSE == *"response"* ]]; then
    echo "✅ Setup Complete! Ollama is responding."
    echo "🎉 You can now proceed with VedantBilling configuration."
else
    echo "⚠️  Ollama might not be running. Start it with 'ollama serve' in a separate terminal."
fi
