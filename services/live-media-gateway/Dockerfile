FROM node:22-bookworm-slim

WORKDIR /srv/live-media
COPY package*.json ./
RUN npm ci --omit=dev
COPY src ./src

EXPOSE 8090 8091 40000-40100/udp
CMD ["npm", "start"]
