# VERSION defines the version for the docker containers.
# To build a specific set of containers with a version,
# you can use the VERSION as an arg of the docker build command (e.g make docker VERSION=0.0.2)
VERSION ?= v0.0.1

# REGISTRY defines the registry where we store our images.
# To push to a specific registry,
# you can use the REGISTRY as an arg of the docker build command (e.g make docker REGISTRY=my_registry.com/username)
# You may also change the default value if you are using a different registry as a default
REGISTRY ?= 505992365906.dkr.ecr.us-east-1.amazonaws.com


# Commands
docker: docker-build docker-push

docker-build:
	docker build -f Dockerfile . --target cli -t ${REGISTRY}/cli:${VERSION}
	docker build -f Dockerfile . --target cron -t ${REGISTRY}/cron:${VERSION}
	docker build -f Dockerfile . --target fpm_server -t ${REGISTRY}/fpm_server:${VERSION}
	docker build -f Dockerfile . --target web_server -t ${REGISTRY}/web_server_gestor:${VERSION}

docker-push:
	docker push ${REGISTRY}/cli:${VERSION}
	docker push ${REGISTRY}/cron:${VERSION}
	docker push ${REGISTRY}/fpm_server:${VERSION}
	docker push ${REGISTRY}/web_server_gestor:${VERSION}