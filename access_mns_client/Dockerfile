# =========================================
# Stage 1: Build the Angular Application
# =========================================
# =========================================
# Stage 1: Build the Angular Application
# =========================================
ARG NODE_VERSION=22.14.0-alpine
ARG NGINX_VERSION=alpine3.21

# Use a lightweight Node.js image for building (customizable via ARG)
FROM node:${NODE_VERSION} AS builder

# Set the working directory inside the container
WORKDIR /app

# Copy package-related files first to leverage Docker's caching mechanism
COPY package.json package-lock.json ./

# Install project dependencies using npm ci (ensures a clean, reproducible install)
RUN npm ci

# Install Angular CLI globally to build the application
RUN npm install -g @angular/cli

# Copy the rest of the application source code into the container
COPY . .

# Declare build-time variables from Railway
ARG RAILWAY_ENVIRONMENT
ARG APP_ENV
ARG API_BASE_URL

# Replace API_BASE_URL placeholder in environment.ts before build
RUN sed -i "s|\${API_BASE_URL}|${API_BASE_URL:-https://localhost/manager/api}|g" src/environments/environment.ts

# Build the Angular application for production
RUN npm run build

# =========================================
# Stage 2: Prepare Nginx to Serve Static Files
# =========================================

FROM nginxinc/nginx-unprivileged:${NGINX_VERSION} AS runner

# Remove default config to avoid conflicts
RUN rm -f /etc/nginx/conf.d/default.conf

# Use a built-in non-root user for security best practices
USER nginx

# Copy custom Nginx config
COPY ./nginx.conf /etc/nginx/nginx.conf

# Copy the static build output from the build stage to Nginx's default HTML serving directory
COPY --chown=nginx:nginx --from=builder /app/dist/*/browser /usr/share/nginx/html

# Expose port 8080 to allow HTTP traffic
# Note: The default NGINX container now listens on port 8080 instead of 80 
EXPOSE 4200

# Start Nginx directly with custom config
ENTRYPOINT ["nginx", "-c", "/etc/nginx/nginx.conf"]
CMD ["-g", "daemon off;"]