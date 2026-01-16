# ===============================
# Build stage
# ===============================
FROM node:20-alpine AS build

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .

# Use production env
COPY .env.production.example .env

RUN npm run build

# ===============================
# Runtime stage (NO NGINX)
# ===============================
FROM alpine:3.19

WORKDIR /frontend

# Copy only built assets
COPY --from=build /app/dist /frontend

# Keep container alive (volume provider)
CMD ["sh", "-c", "while true; do sleep 3600; done"]
