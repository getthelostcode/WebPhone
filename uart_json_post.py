import serial
import requests
import json
import re
from datetime import datetime
import time

# 修改为你的串口设备
SERIAL_PORT = "/dev/ttyAMA3"
BAUD_RATE = 115200
POST_URL = "http://maoyun.ip2.one/save_sms.php"

# 打开串口
ser = serial.Serial(SERIAL_PORT, BAUD_RATE, timeout=1)
print(f"📡 Listening on {SERIAL_PORT}...")

buffer = ""

# 将 GSM 时间格式转换为 MySQL DATETIME 格式
def convert_time(gsm_time_str):
    try:
        dt = datetime.strptime(gsm_time_str[:17], "%y/%m/%d,%H:%M:%S")
        return dt.strftime("%Y-%m-%d %H:%M:%S")
    except Exception as e:
        print("❌ Time format error:", gsm_time_str, e)
        return "1970-01-01 00:00:00"  # fallback

while True:
    try:
        char = ser.readline().decode('gbk', errors='ignore')
        if not char:
            continue
        buffer += char

        # 判断是否完整 JSON （以 } 结尾，且包含开头 {）
        if "{" in buffer and "}" in buffer and buffer.strip().endswith("}"):
            try:
                data = json.loads(buffer.strip())
                print("📨 JSON received:", data)

                # 时间字段格式化
                if re.match(r"\d{2}/\d{2}/\d{2},\d{2}:\d{2}:\d{2}", data.get("time", "")):
                    data["time"] = convert_time(data["time"])

                # 发送 POST 请求
                response = requests.post(POST_URL, data=data)
                if response.ok:
                    print("✅ Posted successfully:", response.text)
                else:
                    print("❌ POST failed:", response.status_code, response.text)
            except Exception as e:
                print("⚠️ JSON decode error or POST failed:", e)

            # 清空 buffer 继续接收下一条
            buffer = ""

    except KeyboardInterrupt:
        print("🔚 Exiting...")
        break
    except Exception as e:
        print("⚠️ Unexpected error:", e)
        time.sleep(1)
