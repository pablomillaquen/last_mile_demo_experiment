FROM ghcr.io/project-osrm/osrm-backend:v5.27.1

EXPOSE 5000

ENTRYPOINT ["osrm-routed", "--algorithm=mld", "/data/valparaiso.osrm"]
