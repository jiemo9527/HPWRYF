# HPWRYF (Hi Proxy, Where Are You From?)

![Project Status](https://img.shields.io/badge/status-active-brightgreen.svg)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

**HPWRYF** 是一个轻量级、高性能的纯前端代理链接解析工具。它可以批量分析代理节点的服务器归属地、运营商（ISP）等信息，并根据这些信息自动生成标准化的备注，支持一键导出结果。

> 🚀 **新特性**：已重构为纯静态 HTML/JS 版本，无需后端服务器，支持多线程并发查询与智能防封禁策略。

## ✨ 功能特性 (Features)

* 🌍 **精准归属地识别**：自动批量查询节点 IP 的国家、城市、运营商 (ISP) 及组织机构 (Org) 信息。
* 📝 **智能备注重写**：根据查询结果自动生成统一格式的备注（例如：`Google - US`），并支持过滤特定无关关键词。
* ⚡ **多线程极速解析**：内置并发控制器，支持同时查询多个链接（默认并发数 5），大幅提升处理速度。
* 🛡️ **智能防封禁**：针对 API 的速率限制（429 Too Many Requests）实现了自动退避与重试机制，确保高成功率。
* 📊 **Excel 导出**：支持将解析后的详细数据（IP、端口、位置、ISP 等）一键导出为 Excel 表格。
* 🔒 **隐私安全**：所有逻辑均在浏览器端（Client-side）执行，您的节点链接不会发送到任何第三方后端服务器。

## 🔗 支持的链接格式 (Supported Protocols)

目前完整支持以下主流代理协议链接的解析与重组：

* **VMess** (vmess:// base64 json)
* **VLESS** (vless://)
* **Shadowsocks** (ss:// 支持 SIP002 及旧版格式)
* **Socks** (socks://)
* **Trojan** (trojan://)

## 🚀 快速开始 (Quick Start)

本项目为纯静态页面，无需安装任何依赖环境（如 Node.js 或 PHP）。

1.  **下载项目**：
    ```bash
    git clone [https://github.com/jiemo9527/HPWRYF.git](https://github.com/jiemo9527/HPWRYF.git)
    ```
2.  **运行**：
    直接双击打开项目目录下的 `index.html` 文件即可在浏览器中使用。

3.  **使用**：
    * 在文本框中粘贴您的节点链接（每行一个）。
    * 点击 **"⚡ 开始极速解析"**。
    * 等待进度条完成，即可复制新链接或导出 Excel。

## 🛠️ 技术栈 (Tech Stack)

* **Core**: HTML5, CSS3, Vanilla JavaScript (ES6+)
* **Data Processing**: SheetJS (xlsx.full.min.js)
* **API**: ipwho.is (Client-side CORS supported)

## 🔮 扩展计划 (Roadmap)

* [x] 自定义备注模板配置 (v2.0 已实装)
* [x] 增加暗黑模式 (Dark Mode) (v2.0 已实装)
* [ ] 支持更多协议格式 (如 Hysteria2, Tuic)
## 📸 预览 (Preview)

![预览图片](/Snipaste.png)

---

**Disclaimer**: This tool is for educational and network diagnostic purposes only. Please use it in compliance with local laws and regulations.