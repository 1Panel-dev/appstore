#!/bin/bash
# This script copies the version from docker-compose.yml to config.json.

app_name=$1
old_version=$2

# find all docker-compose files under apps/$app_name (there should be only one)
docker_compose_files=$(find apps/$app_name/$old_version -name docker-compose.yml)

for docker_compose_file in $docker_compose_files
do
	# Assuming that the app version will be from the first docker image
	first_service=$(yq '.services | keys | .[0]' $docker_compose_file)

	image=$(yq .services.$first_service.image $docker_compose_file)

	# Only apply changes if the format is <image>:<version>
	if [[ "$image" == *":"* ]]; then
	  version=$(cut -d ":" -f2- <<< "$image")

	  # Trim the "v" prefix
	  trimmed_version=${version/#"v"}

      target_version=$trimmed_version
      if [[ "$app_name" == "vllm" ]]; then
        case "$image" in
          vllm/vllm-openai:*) target_version="nvidia-$trimmed_version" ;;
          intel/llm-scaler-vllm:*) target_version="intel-$trimmed_version" ;;
          quay.io/ascend/vllm-ascend:*) target_version="ascend-$trimmed_version" ;;
        esac
      fi

      mv apps/$app_name/$old_version apps/$app_name/$target_version
    fi
done
