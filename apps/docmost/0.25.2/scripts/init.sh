NEW_SECRET=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | head -c 32)
if grep -q "APP_SECRET=" .env; then
    if [[ "$(uname)" == "Darwin" ]]; then
        sed -i '' "s/APP_SECRET=.*/APP_SECRET=$NEW_SECRET/" .env
    else
        sed -i "s/APP_SECRET=.*/APP_SECRET=$NEW_SECRET/" .env
    fi
else
    echo "APP_SECRET=$NEW_SECRET" >> .env
fi