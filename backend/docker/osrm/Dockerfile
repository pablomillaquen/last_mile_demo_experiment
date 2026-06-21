FROM ghcr.io/project-osrm/osrm-backend:v5.27.1

RUN apt-get update && apt-get install -y curl && rm -rf /var/lib/apt/lists/*

EXPOSE 5000

ENTRYPOINT ["osrm-routed", "--algorithm=mld", "/data/valparaiso.osrm"]
