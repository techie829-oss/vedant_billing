# Build Stage
FROM node:20-alpine as build-stage

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .

# Load production env vars from .env.production.example (user will need to ensure this file exists or args are passed)
# Ideally we pass args, but simplified here
COPY .env.production.example .env

RUN npm run build

# Production Stage
FROM nginx:stable-alpine as production-stage

COPY --from=build-stage /app/dist /usr/share/nginx/html
# Copy custom nginx config if we had one for JUST frontend, but we are using a main nginx proxy.
# So this might just serve static files. 
# We'll use the main Nginx service to route traffic, so this container might just expose port 80 with files.

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
