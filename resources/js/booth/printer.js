/**
 * Thermal Printer Utilities - WebUSB and Web Bluetooth (BLE)
 * For ESC/POS compatible thermal printers (58mm)
 */

const PRINTER_STATE = {
    type: null, // 'usb' | 'bluetooth'
    device: null,
    characteristic: null, // BLE write characteristic
    server: null, // BLE GATT server
};

/** Common BLE service UUIDs for thermal printers */
const BLE_SERVICES = [
    "6e400001-b5a3-f393-e0a9-e50e24dcca9e", // Nordic UART
    "49535343-fe7d-4ae5-8fa9-9fafd205e455", // HM-10 / Serial
    "e7810a71-73ae-499d-8c15-faa9aef0c3f2", // Custom thermal (XP-D4601B, etc)
    "0000ffe0-0000-1000-8000-00805f9b34fb", // Common BLE serial
];

/** BLE: chunk besar + tanpa delay = lebih cepat (mirip kecepatan USB) */
const BLE_CHUNK_SIZE = 130; // Max ATT MTU, didukung setelah negosiasi saat connect
const BLE_CHUNK_DELAY_MS = 5; // Tidak perlu delay dengan chunk besar

/** Nordic UART TX - karakteristik untuk menulis ke BLE serial */
const NORDIC_UART_TX = "6e400002-b5a3-f393-e0a9-e50e24dcca9e";

/**
 * Connect to thermal printer via WebUSB
 * @param {number} vendorId - USB vendor ID (default: 0x0418)
 */
export async function connectPrinterUSB(vendorId = 0x0418) {
    if (!navigator.usb) throw new Error("WebUSB tidak didukung.");

    // Coba ambil device yang sudah pernah dipair (tanpa dialog)
    const paired = await navigator.usb.getDevices();
    const existing = paired.find((d) => d.vendorId === vendorId);
    const device =
        existing ??
        (await navigator.usb.requestDevice({ filters: [{ vendorId }] }));

    if (!device.opened) {
        await device.open();
        if (device.configuration === null) await device.selectConfiguration(1);
        // Cari interface & endpoint OUT otomatis
        let interfaceNumber = null,
            endpointNumber = null;
        for (const iface of device.configuration.interfaces) {
            for (const alt of iface.alternates) {
                const ep = alt.endpoints.find((e) => e.direction === "out");
                if (ep) {
                    interfaceNumber = iface.interfaceNumber;
                    endpointNumber = ep.endpointNumber;
                    break;
                }
            }
            if (endpointNumber !== null) break;
        }
        if (endpointNumber === null)
            throw new Error("Endpoint OUT tidak ditemukan.");
        await device.claimInterface(interfaceNumber);
        PRINTER_STATE.endpointNumber = endpointNumber;
    }

    PRINTER_STATE.type = "usb";
    PRINTER_STATE.device = device;
}

/**
 * Connect to thermal printer via Web Bluetooth (BLE)
 */
export async function connectPrinterBLE() {
    if (!navigator.bluetooth) {
        throw new Error(
            "Web Bluetooth tidak didukung. Gunakan Chrome atau Edge.",
        );
    }
    await disconnectPrinter();

    const device = await navigator.bluetooth.requestDevice({
        acceptAllDevices: true,
        optionalServices: BLE_SERVICES,
    });

    const server = await device.gatt.connect();
    const services = await server.getPrimaryServices();

    let writeChar = null;
    let nordicTx = null;

    for (const svc of services) {
        const chars = await svc.getCharacteristics();
        for (const c of chars) {
            if (!c.properties.write && !c.properties.writeWithoutResponse)
                continue;
            if (c.uuid.toLowerCase() === NORDIC_UART_TX) {
                nordicTx = c;
                break;
            }
            if (!writeChar) writeChar = c;
        }
    }

    const chosen = nordicTx || writeChar;
    if (!chosen) {
        await server.disconnect();
        throw new Error(
            "Tidak ditemukan karakteristik untuk menulis data. Pastikan printer BLE mendukung ESC/POS.",
        );
    }

    PRINTER_STATE.type = "bluetooth";
    PRINTER_STATE.device = device;
    PRINTER_STATE.characteristic = chosen;
    PRINTER_STATE.server = server;
    console.log("[Printer] BLE connected:", device.name, "char:", chosen.uuid);
}

/**
 * Auto-reconnect BLE dari device yang sudah pernah dipair
 */
export async function autoReconnectBLE() {
    if (!navigator.bluetooth?.getDevices) return false;

    const devices = await navigator.bluetooth.getDevices();
    if (!devices.length) return false;

    for (const device of devices) {
        try {
            const server = await device.gatt.connect();
            const services = await server.getPrimaryServices();

            let writeChar = null,
                nordicTx = null;
            for (const svc of services) {
                const chars = await svc.getCharacteristics();
                for (const c of chars) {
                    if (
                        !c.properties.write &&
                        !c.properties.writeWithoutResponse
                    )
                        continue;
                    if (c.uuid.toLowerCase() === NORDIC_UART_TX) {
                        nordicTx = c;
                        break;
                    }
                    if (!writeChar) writeChar = c;
                }
            }

            const chosen = nordicTx || writeChar;
            if (!chosen) continue;

            PRINTER_STATE.type = "bluetooth";
            PRINTER_STATE.device = device;
            PRINTER_STATE.characteristic = chosen;
            PRINTER_STATE.server = server;

            // Reconnect otomatis jika terputus
            device.addEventListener("gattserverdisconnected", async () => {
                console.warn("[Printer] BLE disconnected, retrying...");
                try {
                    await device.gatt.connect();
                } catch {}
            });

            console.log("[Printer] BLE auto-reconnected:", device.name);
            return true;
        } catch (err) {
            console.warn("[Printer] Auto-reconnect failed:", err);
        }
    }
    return false;
}

/**
 * Connect via USB (alias for backward compatibility)
 */
export async function connectPrinter(vendorId = 0x0418) {
    return connectPrinterUSB(vendorId);
}

/**
 * Check if printer is connected
 */
export function isPrinterConnected() {
    return PRINTER_STATE.device !== null;
}

/**
 * Get current printer connection type
 * @returns {'usb'|'bluetooth'|null}
 */
export function getPrinterType() {
    return PRINTER_STATE.type;
}

/**
 * Check if WebUSB is available
 */
export function isWebUSBAvailable() {
    return !!navigator.usb;
}

/**
 * Check if Web Bluetooth is available
 */
export function isWebBluetoothAvailable() {
    return !!navigator.bluetooth;
}

/**
 * Disconnect printer
 */
export async function disconnectPrinter() {
    if (!PRINTER_STATE.device) return;
    try {
        if (
            PRINTER_STATE.type === "bluetooth" &&
            PRINTER_STATE.server?.connected
        ) {
            PRINTER_STATE.server.disconnect();
        } else if (
            PRINTER_STATE.type === "usb" &&
            PRINTER_STATE.device?.opened
        ) {
            await PRINTER_STATE.device.close();
        }
    } catch (err) {
        console.warn("[Printer] Disconnect error:", err);
    }
    PRINTER_STATE.type = null;
    PRINTER_STATE.device = null;
    PRINTER_STATE.characteristic = null;
    PRINTER_STATE.server = null;
    console.log("[Printer] Disconnected");
}

function getPrintSettings() {
    return {
        threshold: parseInt(
            localStorage.getItem("photobooth_printThreshold") ?? "128",
            10,
        ),
        brightness: parseFloat(
            localStorage.getItem("photobooth_printBrightness") ?? "100",
        ),
    };
}

/**
 * Floyd-Steinberg Dithering
 * @param {Uint8ClampedArray} data - ImageData RGBA array
 * @param {number} width - Image width
 * @param {number} height - Image height
 */
function floydSteinbergDithering(data, w, h, threshold = 160) {
    const idx = (x, y) => (w * y + x) * 4;

    for (let y = 0; y < h; y++) {
        for (let x = 0; x < w; x++) {
            const i = idx(x, y);
            const old = data[i];
            const nw = old < threshold ? 0 : 255;
            const err = old - nw;

            data[i] = data[i + 1] = data[i + 2] = nw;

            const add = (x2, y2, f) => {
                if (x2 >= 0 && x2 < w && y2 >= 0 && y2 < h) {
                    const j = idx(x2, y2);
                    data[j] =
                        data[j + 1] =
                        data[j + 2] =
                            Math.max(0, Math.min(255, data[j] + err * f));
                }
            };

            add(x + 1, y, 7 / 16);
            add(x - 1, y + 1, 3 / 16);
            add(x, y + 1, 5 / 16);
            add(x + 1, y + 1, 1 / 16);
        }
    }
}

/**
 * Convert image to ESC/POS format (1-bit bitmap with dithering)
 * @param {string} imageDataUrl - Data URL of the image
 * @param {number} width - Target width in pixels (default: 680 for 58mm printer)
 * @returns {Promise<Uint8Array>} ESC/POS command bytes
 */
async function convertToESCPos(imageDataUrl, width = 640) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => {
            try {
                // Create canvas to resize and convert image
                const canvas = document.createElement("canvas");
                const ctx = canvas.getContext("2d");
                if (!ctx) {
                    reject(new Error("Could not get canvas context"));
                    return;
                }

                // Calculate height maintaining aspect ratio
                const aspectRatio = img.height / img.width;
                const height = Math.min(Math.round(width * aspectRatio), 1800);

                canvas.width = width;
                canvas.height = height;

                // Draw and resize image
                ctx.drawImage(img, 0, 0, width, height);

                // Get image data
                const imageData = ctx.getImageData(0, 0, width, height);
                const data = imageData.data;
                const { threshold, brightness } = getPrintSettings();

                for (let i = 0; i < data.length; i += 4) {
                    const alpha = data[i + 3] / 255;
                    data[i] = Math.round(data[i] * alpha + 255 * (1 - alpha));
                    data[i + 1] = Math.round(
                        data[i + 1] * alpha + 255 * (1 - alpha),
                    );
                    data[i + 2] = Math.round(
                        data[i + 2] * alpha + 255 * (1 - alpha),
                    );
                    data[i + 3] = 255;
                }

                for (let i = 0; i < data.length; i += 4) {
                    const r = data[i],
                        g = data[i + 1],
                        b = data[i + 2];
                    let gray = Math.round(0.299 * r + 0.587 * g + 0.114 * b);
                    gray = Math.min(255, gray + brightness);
                    data[i] = data[i + 1] = data[i + 2] = gray;
                }

                // Apply Floyd-Steinberg dithering
                floydSteinbergDithering(data, width, height, threshold);

                // Convert to 1-bit bitmap (8 pixels per byte)
                const bytesPerRow = Math.ceil(width / 8);
                const bitmapData = new Uint8Array(bytesPerRow * height);

                for (let y = 0; y < height; y++) {
                    for (let x = 0; x < width; x++) {
                        const idx = (y * width + x) * 4;
                        const pixelValue = data[idx]; // Get grayscale value from R channel
                        const bit = pixelValue < 128 ? 1 : 0; // Inverted for thermal printer (black = 1)
                        const byteIdx = Math.floor(x / 8);
                        const bitPos = 7 - (x % 8);
                        bitmapData[y * bytesPerRow + byteIdx] |= bit << bitPos;
                    }
                }

                // Build ESC/POS command
                const commands = [];

                // Initialize printer
                commands.push(0x1b, 0x40); // ESC @ (Initialize)

                // Set center alignment
                commands.push(0x1b, 0x61, 0x01); // ESC a 1 (Center align)

                // Set image mode
                commands.push(0x1d, 0x76, 0x30, 0x00); // GS v 0 (Print raster image)

                // Image width (LSB, MSB)
                const widthLow = bytesPerRow & 0xff;
                const widthHigh = (bytesPerRow >> 8) & 0xff;
                commands.push(widthLow, widthHigh);

                // Image height (LSB, MSB)
                const heightLow = height & 0xff;
                const heightHigh = (height >> 8) & 0xff;
                commands.push(heightLow, heightHigh);

                // Append bitmap data
                commands.push(...Array.from(bitmapData));

                // Feed paper and cut (if supported)
                // commands.push(0x1d, 0x56, 0x00); // GS V 0 (Cut paper)
                // commands.push(0x0a); // Line feed

                commands.push(0x1b, 0x64, 0x04); // ESC d 4 — feed 4 lines
                commands.push(0x1d, 0x56, 0x41, 0x00); // GS V 65 0 — full cut

                // Reset alignment to left (optional, for next print)
                commands.push(0x1b, 0x61, 0x00); // ESC a 0 (Left align)

                resolve(new Uint8Array(commands));
            } catch (error) {
                reject(error);
            }
        };

        img.onerror = () => {
            reject(new Error("Failed to load image"));
        };

        img.src = imageDataUrl;
    });
}

/**
 * Send ESC/POS data to printer (USB or BLE)
 * @param {Uint8Array} data
 */
async function sendToPrinter(data) {
    if (PRINTER_STATE.type === "usb") {
        const iface = PRINTER_STATE.device.configuration.interfaces[0];
        const endpoint = iface.alternates[0].endpoints.find(
            (e) => e.direction === "out",
        );
        await PRINTER_STATE.device.transferOut(endpoint.endpointNumber, data);
        return;
    }
    if (PRINTER_STATE.type === "bluetooth" && PRINTER_STATE.characteristic) {
        const char = PRINTER_STATE.characteristic;
        const useNoResponse = char.properties.writeWithoutResponse;

        const writeChunk = async (chunk) => {
            try {
                if (useNoResponse && chunk.byteLength <= 512) {
                    await char.writeValueWithoutResponse(chunk);
                } else {
                    await char.writeValue(chunk);
                }
            } catch (e) {
                if (useNoResponse && e.name !== "NetworkError") {
                    await char.writeValue(chunk);
                } else {
                    throw e;
                }
            }
        };

        for (let i = 0; i < data.length; i += BLE_CHUNK_SIZE) {
            const chunk = data.slice(i, i + BLE_CHUNK_SIZE);
            await writeChunk(chunk);
            if (BLE_CHUNK_DELAY_MS > 0 && i + BLE_CHUNK_SIZE < data.length) {
                await new Promise((r) => setTimeout(r, BLE_CHUNK_DELAY_MS));
            }
        }
        return;
    }
    throw new Error(
        "Printer tidak terhubung atau tidak mendukung pengiriman data.",
    );
}

/**
 * Print photostrip to thermal printer
 * @param {string} imageDataUrl - Data URL of the photostrip image
 * @param {number} quantity - Number of copies to print
 * @returns {Promise<void>} Promise that resolves when print is complete
 */
export async function printPhotostrip(imageDataUrl, quantity = 1) {
    if (!PRINTER_STATE.device) {
        throw new Error(
            "Printer belum terhubung. Silakan hubungkan printer terlebih dahulu.",
        );
    }

    if (
        PRINTER_STATE.type === "bluetooth" &&
        PRINTER_STATE.server &&
        !PRINTER_STATE.server.connected
    ) {
        throw new Error(
            "Koneksi printer terputus. Silakan hubungkan ulang printer di Pengaturan.",
        );
    }

    const escposData = await convertToESCPos(imageDataUrl);

    for (let i = 0; i < quantity; i++) {
        await sendToPrinter(escposData);
        if (i < quantity - 1) {
            await new Promise((r) => setTimeout(r, 500));
        }
    }

    console.log(`[Printer] Printed ${quantity} copy/copies`);
}

/**
 * Print photostrip from File/Blob
 * @param {File|Blob} photoFile - File or Blob object of the photostrip
 * @param {number} quantity - Number of copies to print
 */
export async function printPhotostripFromFile(photoFile, quantity = 1) {
    // Convert File/Blob to data URL
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = async (e) => {
            try {
                const dataUrl = e.target?.result;
                await printPhotostrip(dataUrl, quantity);
                resolve();
            } catch (error) {
                reject(error);
            }
        };
        reader.onerror = () => reject(new Error("Failed to read file"));
        reader.readAsDataURL(photoFile);
    });
}
