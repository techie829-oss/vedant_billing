#!/bin/bash

# VedantBilling - Mac OCR & AI Stack Installer
# Requires Homebrew

echo "🚀 Starting VedantBilling OCR Setup for Mac..."

# Check for Homebrew
if ! command -v brew &> /dev/null; then
    echo "❌ Homebrew is not installed. Please install it first: https://brew.sh/"
    exit 1
fi

# 1. Install Tesseract OCR
echo "📖 Checking Tesseract OCR..."
if ! command -v tesseract &> /dev/null; then
    brew install tesseract
    echo "✅ Tesseract installed."
else
    echo "✅ Tesseract is already installed."
fi

# Verify
tesseract --version

# 2. Install Ollama
echo "🦙 Checking Ollama..."
if ! command -v ollama &> /dev/null; then
    brew install ollama
    echo "✅ Ollama installed."
else
    echo "✅ Ollama is already installed."
fi

# 3. Start Ollama Service
echo "🚀 Ensure Ollama is running..."
brew services start ollama 2>/dev/null

# Wait briefly for service to start
sleep 2

# 4. Pull Llama 3 Model
echo "🧠 Downloading Llama 3 Model (3.8GB) - This may take time..."
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
    echo "⚠️  Ollama might not be running. Try running 'ollama serve' manually."
fi
