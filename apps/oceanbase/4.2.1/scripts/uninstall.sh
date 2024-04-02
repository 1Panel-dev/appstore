source ./.env 

LOG='./uninstall.log'

HOME_PATH=`realpath $PWD/data/ob/`
echo "remove $HOME_PATH" >> $LOG
rm -fr $PWD/data/ob/ 2>&1 >> $LOG || exit 10