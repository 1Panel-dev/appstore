source ./.env

OPEN_TOKEN_FILE="${PWD}/data/myopentoken.txt"
REFRESH_TOKEN_FILE="${PWD}/data/mytoken.txt"
TEMP_FOLD_ID_FILE="${PWD}/data/temp_transfer_folder_id.txt"

if [ -f ${OPEN_TOKEN_FILE} ]; then
	mv ${OPEN_TOKEN_FILE} "${OPEN_TOKEN_FILE}.1"
fi

if [ -f ${REFRESH_TOKEN_FILE} ]; then
	mv ${REFRESH_TOKEN_FILE} "${REFRESH_TOKEN_FILE}.1"
fi

if [ -f ${TEMP_FOLD_ID_FILE} ]; then
	mv ${TEMP_FOLD_ID_FILE} "${TEMP_FOLD_ID_FILE}.1"
fi

echo "${XIAOYA_OPEN_TOKEN}" > ${OPEN_TOKEN_FILE}
echo "${XIAOYA_REFRESH_TOKEN}" > ${REFRESH_TOKEN_FILE}
echo "${XIAOYA_TEMP_FOLDER_ID}" > ${TEMP_FOLD_ID_FILE}
