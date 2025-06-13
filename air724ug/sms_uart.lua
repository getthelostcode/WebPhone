local sms_uart = {}

local uart = require "uart"
local sms = require "sms"


-- 使用 UART1，波特率115200
local uart_id = 1
local baudrate = 115200

-- 初始化 UART
local function init_uart()
    uart.setup(uart_id, baudrate, 8, 1, uart.PAR_NONE)
end

-- 短信接收回调函数
local function on_sms(num, content, datetime)
    log.info("sms_uart", "SMS from", num)
    log.info("sms_uart", "Content", content)
    log.info("sms_uart", "Time", datetime or "N/A")

    -- 构造 JSON 格式的数据
    local sms_data = {
        simid = sim.getImsi(), -- 可替换为真实 SIM ID
        type = "inbox",
        number = num or "unknown",
        time = datetime or "N/A",
        content = content or "nil"
    }

    -- JSON 编码并通过 UART1 发送
    local json_str = json.encode(sms_data) .. "\r\n"
    uart.write(uart_id, json_str)
end

-- 初始化函数
function sms_uart.init()
    init_uart()
    sms.setNewSmsCb(on_sms)
    log.info("sms_uart", "SMS to UART (JSON) initialized")
end

return sms_uart
