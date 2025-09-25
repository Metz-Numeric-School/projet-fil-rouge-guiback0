FROM nginx:alpine

# Install curl for health checks
RUN apk add --no-cache curl

# Copy nginx configuration (use Railway-specific config)
COPY nginx.railway.conf /etc/nginx/nginx.conf

# Create necessary directories
RUN mkdir -p /var/log/nginx /var/cache/nginx

# Set proper permissions
RUN chown -R nginx:nginx /var/cache/nginx /var/log/nginx

# Expose port (Railway assigns the PORT env variable)
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:80/health || exit 1

# Start nginx
CMD ["nginx", "-g", "daemon off;"]