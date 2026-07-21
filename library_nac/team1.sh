#!/bin/bash

# ==========================================
# KONFIGURASI ADMIN
# ==========================================
ADMIN_ID="1170932931"

# Fungsi Utama untuk Menjalankan Bot
run_bot() {
    local TOKEN="$1"
    local TEAM_NAME="$2"
    local USER_DATA="/home/miquela/web4/users_${TEAM_NAME}.txt"
    local API_URL="https://api.telegram.org/bot$TOKEN"
    local OFFSET=0

    # Ambil referensi array yang dilempar ke fungsi
    shift 2
    local q_ref=("$@")
    # Karena Bash sulit melempar multiple arrays, kita definisikan ulang di dalam scope fungsi
    # atau menggunakan data yang sudah di-set di variabel global berdasarkan TEAM_NAME.
    
    # Mapping Data Soal Berdasarkan Tim
    if [ "$TEAM_NAME" == "TIM_1" ]; then
        local questions=("Berapakah ISBN dari buku tertua yang berada di kategori mystery?" "apa nama database yang digunakan sistem?" "Nilai cookie apa yang memberikan akses ke area tersembunyi?" "Apa kredensial login lengkap untuk akun librarian? (Format: username:password)" "Nilai apa yang menandakan akses Admin telah aktif?" "File apa yang dapat kamu include melalui parameter debug?" "Di manakah lokasi buku dengan kode arsip ARC-2008-MRT disimpan?" "Alamat IP apa yang digunakan oleh user dengan ID 6 pada 15 Januari 2024 pukul 14:30?" "Apa nilai master_key yang terdapat pada konfigurasi sistem yang tersembunyi di System config admin panel?" "Tuliskan formula yang memungkinkan Anda memalsukan X-Privilege-Token" "Apa email pengguna yang meminjam buku yang ditulis oleh penulis dari buku mystery tertua?")
        local answers=("978-0486284736" "library_ctf" "true" "librarian_admin:admin" "granted" "config.php" "SPECIAL-COLLECTION-C1" "198.51.100.77" "MASTER-KEY-2024-LIBRARY-NAC" "SHA256(user_id + username + role)" "fan@ctf.id")
        local hints=("sql injection" "information disclosure" "cookie" "source code" "response server" "url" "arsip" "idor, log" "chain attack, privilege escalation" "encode, credential" "sqlmap nested")
    else
        # DATA TIM 2
        local questions=("Apa restoration key yang tersimpan di dalam file backup lama pada website ini?" "Apa codename proyek top-secret yang tercantum di dalam memo internal pada database?" "Apa recovery email dari service account yang tersembunyi di website ini?" "Apa judul draft yang belum dipublikasikan di akun editor sarah?" "Berapa versi (version) dari internal API service yang tidak dapat diakses dari luar?" "Berapa nilai API_SECRET yang ditemukan setelah berhasil melewati upload filter dan membaca file kredensial di server?" "Apa isi file /etc/nac/service_token yang dapat dibaca melalui fitur import RSS?" "Apa nilai m yang berhasil didekripsi dari tabel admin_secrets, menggunakan kunci enkripsi yang terdapat di site_settings?" "Apa passphrase yang tercatat di dalam file /var/log/nac/admin_actions.log? (format jawab: key | url parameter)" "Apa kode akses vault yang ditemukan di dalam file /opt/nac/final_directive.txt setelah mengakses halaman diagnostik admin?" "Apa rahasia terakhir yang ditemukan di halaman /admin/vault.php setelah berhasil melakukan eskalasi privilege ke admin?")
        local answers=("nac_restoration_2024" "project_lighthouse" "sysbot@internal.nac.local" "The Hidden Archive of Digital Artifacts" "v3.7.2-ptolemy" "sk_live_pharos_9x7k2m" "tkn_hypatia_scroll_88f2" "the_great_library_never_burned" "ptolemy_dynasty_305bc | ?page=../../../../var/log/nac/admin_actions.log" "127.0.0.1 ; cat /opt/nac/final_directive.txt" "knowledge_is_the_true_eternal_flame")
        local hints=("Common File, Encode" "Sql Injection" "IDOR, Out of box" "Brute Force" "Editor, SSRF" "Upload File, Case Sensitive" "XXE" "XOR" "Lfi" "Command Injection, Url" "Cookie Forgery, Broken Access Control")
    fi

    touch "$USER_DATA"
    echo "Bot $TEAM_NAME Berjalan..."

    while true; do
        UPDATES=$(curl -s --max-time 15 "$API_URL/getUpdates?offset=$OFFSET&timeout=30")

        if [ -z "$UPDATES" ] || [ "$(echo "$UPDATES" | jq -r '.ok')" != "true" ]; then
            sleep 2; continue
        fi

        COUNT=$(echo "$UPDATES" | jq '.result | length')
        for (( i=0; i<$COUNT; i++ )); do
            UPDATE_ID=$(echo "$UPDATES" | jq ".result[$i].update_id")
            CHAT_ID=$(echo "$UPDATES" | jq -r ".result[$i].message.chat.id")
            TG_USER=$(echo "$UPDATES" | jq -r ".result[$i].message.from.username // .result[$i].message.from.first_name")
            TEXT=$(echo "$UPDATES" | jq -r ".result[$i].message.text")
            OFFSET=$((UPDATE_ID + 1))

            if [ "$TEXT" == "null" ] || [ -z "$TEXT" ]; then continue; fi

            STATUS=$(grep "^$CHAT_ID:" "$USER_DATA" | cut -d: -f2)
            LEVEL=$(grep "^$CHAT_ID:" "$USER_DATA" | cut -d: -f3)
            PLAYER_NAME=$(grep "^$CHAT_ID:" "$USER_DATA" | cut -d: -f4)

            # --- HELPER SEND MESSAGE ---
            send_msg() {
                curl -s -X POST "$API_URL/sendMessage" -d "chat_id=$1" -d "text=$2" -d "parse_mode=HTML" > /dev/null
            }

            # --- LOGIKA GAME ---
            if [ "$TEXT" == "/start" ]; then
                if [ "$STATUS" == "LVL" ]; then
                    send_msg "$CHAT_ID" "Halo kembali, <b>$PLAYER_NAME</b>! 👋%0A%0ASilakan lanjut di <b>$TEAM_NAME</b>."
                else
                    echo "$CHAT_ID:REG:0:None" >> "$USER_DATA"
                    send_msg "$CHAT_ID" "<b>Selamat datang di CTF $TEAM_NAME!</b> 🚩%0AMasukkan Nickname kamu:"
                fi
            elif [ "$STATUS" == "REG" ]; then
                CLEAN_NAME=$(echo "$TEXT" | tr -d ':')
                sed -i "s/^$CHAT_ID:.*/$CHAT_ID:LVL:0:$CLEAN_NAME/" "$USER_DATA"
                send_msg "$CHAT_ID" "Halo <b>$CLEAN_NAME</b>! Ketik <code>/soal</code>."
                send_msg "$ADMIN_ID" "👤 <b>USER BARU ($TEAM_NAME)</b>%0ANama: $CLEAN_NAME%0AUser: @$TG_USER"
            elif [ "$STATUS" == "LVL" ]; then
                case "$TEXT" in
                    "/soal")
                        if [ "$LEVEL" -lt "${#questions[@]}" ]; then
                            send_msg "$CHAT_ID" "<b>[ FLAG $((LEVEL + 1)) ]</b>%0A${questions[$LEVEL]}"
                        else
                            send_msg "$CHAT_ID" "🎉 Selesai!"
                        fi ;;
                    "/hint")
                        send_msg "$CHAT_ID" "💡 <b>Hint:</b> <i>${hints[$LEVEL]}</i>" ;;
                    *)
                        if [ "$LEVEL" -lt "${#questions[@]}" ]; then
                            if [ "$TEXT" == "${answers[$LEVEL]}" ]; then
                                NEW_LEVEL=$((LEVEL + 1))
                                sed -i "s/^$CHAT_ID:.*/$CHAT_ID:LVL:$NEW_LEVEL:$PLAYER_NAME/" "$USER_DATA"
                                send_msg "$CHAT_ID" "✅ <b>BENAR!</b>"
                            else
                                send_msg "$CHAT_ID" "❌ <b>SALAH!</b>"
                                send_msg "$ADMIN_ID" "📢 <b>INPUT [$TEAM_NAME]</b>%0AUser: $PLAYER_NAME%0ALvl: $((LEVEL+1))%0AIn: <code>$TEXT</code>"
                            fi
                        fi ;;
                esac
            fi
        done
    done
}

# ==========================================
# MENJALANKAN DUA BOT (PARALLEL)
# ==========================================

# Tim 1
run_bot "8505151741:AAENbzPxe91ijR6L92cr1wfGwQp87_Wu0B4" "TIM_1" &

# Tim 2
run_bot "7717109001:AAFrbfYIIc9m11r0FPf9PuEv6YwanryPhJM" "TIM_2" &

# Menjaga script agar tidak exit
wait
