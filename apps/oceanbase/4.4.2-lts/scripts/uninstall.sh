source ./.env 

LOG='./uninstall.log'

HOME_PATH=`realpath $OB_MOUNT_PATH/ob/`
echo "remove $HOME_PATH" >> $LOG
rm -fr $OB_MOUNT_PATH/ob/ 2>&1 >> $LOG || exit 10
