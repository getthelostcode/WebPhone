local sys = require "sys"
local net = require "net"
local socket = require "socket"

-- 引入我们自己写的模块
local sms_uploader = require "sms_uploader"

-- 网络就绪检测
sys.taskInit(function()
    while not socket.isReady() do
        log.info("net", "waiting for network...")
        sys.wait(2000)
    end
    log.info("net", "network ready")

    -- 初始化短信上传模块（传入服务器 URL）
    sms_uploader.init("http://192.168.88.9/api/save_sms.php")
end)

sys.run()
