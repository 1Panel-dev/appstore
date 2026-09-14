#!/bin/bash

source ./.env
LOG='./init.log'

mkdir -p $PWD/data/.obd/cluster 2>&1 >> $LOG  || exit 11
if [ "x$OB_INSTALL_PATH" != "x" ]; then
    HOME_PATH="$OB_INSTALL_PATH/ob/$CONTAINER_NAME"
    echo "link $HOME_PATH to $OB_MOUNT_PATH/ob" >> $LOG
    mkdir -p $HOME_PATH 2>&1 >> $LOG || exit 10
    ln -sf $HOME_PATH ./data/ob 2>&1 >> $LOG  || exit 12
    echo "HOME_PATH=$HOME_PATH" >> ./.env
else
    mkdir -p $OB_MOUNT_PATH/ob 2>&1 >> $LOG  || exit 10
fi

error_count=0
log_error() {
    echo "[ERROR] $1" >> $LOG
    error_count=$((error_count + 1))
}

port_check() {
    local port="$1"
    local hex_port
    hex_port=$(printf "%04X" "$port")
    if grep -q ":${hex_port}" /proc/net/tcp* || grep -q ":${hex_port}" /proc/net/udp*; then
        log_error "Port ${port} is already in use."
        return 1
    fi
    return 0
}

dir_check() {
    local dir="$1"
    local check_disk_space="$2"
    local required_disk_gb="$3"

    # Check parent directory permissions by trying to create it
    if ! mkdir -p "$(dirname "$dir")"; then
        log_error "Could not create parent directory for '${dir}'. Check permissions."
        return 1
    fi

    if [ -e "$dir" ]; then
        if [ -d "$dir" ]; then
            # Check if directory is empty
            if [ "$(ls -A "$dir")" ]; then
                log_error "Directory '${dir}' exists and is not empty."
            fi
            # Check for read/write permissions
            if [ ! -r "$dir" ] || [ ! -w "$dir" ]; then
                log_error "Current user does not have read/write permissions for '${dir}'."
            fi
        else
            log_error "A file, not a directory, already exists at '${dir}'."
            return 1
        fi
    fi

    if [[ "$check_disk_space" == "true" ]]; then
        mkdir -p "$dir"
        local available_disk_gb
        available_disk_gb=$(df -BG "$dir" | awk 'NR==2 {print $4}' | tr -d 'G')

        if [ "$available_disk_gb" -lt "$required_disk_gb" ]; then
            log_error "Insufficient disk space for '${dir}'. Available: ${available_disk_gb}G, Required: > ${required_disk_gb}G."
        fi
    fi
}

echo "Starting server pre-check for OceanBase installation..."

echo "Checking required ports..."
ports_to_check=(2881)
for port in "${ports_to_check[@]}"; do
    port_check "$port"
done

echo "Checking system memory..."
required_mem_gb=$OB_MEMORY_LIMIT
available_mem_gb=$(free -g | awk '/^Mem:/{print $7}')
if [ "$available_mem_gb" -lt "$required_mem_gb" ]; then
    log_error "Available memory is ${available_mem_gb}G, which is less than the minimum requirement of ${required_mem_gb}G (OB_MEMORY_LIMIT)."
fi
echo "-> Available memory: ${available_mem_gb}G."

echo "Checking directories and disk space..."

required_datafile_gb=$OB_DATAFILE_SIZE
required_log_disk_gb=$OB_LOG_DISK_SIZE
disk_required_gb=$((required_datafile_gb + required_log_disk_gb))

echo "-> Checking OceanBase mount path: ${OB_MOUNT_PATH}"
dir_check "${OB_MOUNT_PATH}/ob" true "$disk_required_gb"

echo ""
echo "----------------------------------------"
if [ "$error_count" -eq 0 ]; then
    echo "Server pre-check completed successfully."
else
    echo "Server pre-check found ${error_count} error(s)."
    echo "Please review the log for details:"
    cat $LOG
    exit 1
fi
