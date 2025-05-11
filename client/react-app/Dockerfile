# ---------- Build ----------  
FROM node:18-alpine AS builder
WORKDIR /app
COPY package*.json ./
ENV NODE_ENV=production
RUN npm ci --legacy-peer-deps
COPY . .
RUN npm run build 
# ---------- Serve ----------  
FROM nginx:stable-alpine
COPY --from=builder /app/build /usr/share/nginx/html
EXPOSE 80
CMD ["nginx","-g","daemon off;"]
