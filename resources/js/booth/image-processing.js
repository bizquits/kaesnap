/**
 * Image merge: multiple photos + frame template.
 * Draws all photos into their respective slots, returns merged data URL.
 * @param {string[]} photoDataUrls - Array of photo data URLs (one per slot)
 * @param {string} frameImageUrl
 * @param {object} options - { photoSlots: [], mirror: false }
 */
export function mergePhotoWithFrame(
    photoDataUrls,
    frameImageUrl,
    options = {},
) {
    const { photoSlots = [], mirror = false } = options;

    // Normalise: support single photo string (backwards compat)
    const photos = Array.isArray(photoDataUrls)
        ? photoDataUrls
        : [photoDataUrls];

    const defaultArea = { x: 0.1, y: 0.15, width: 0.8, height: 0.7 };

    return new Promise((resolve, reject) => {
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");
        if (!ctx) {
            reject(new Error("No canvas context"));
            return;
        }

        const templateImg = new Image();
        templateImg.crossOrigin = "anonymous";

        templateImg.onload = async () => {
            canvas.width = templateImg.width;
            canvas.height = templateImg.height;

            // Draw template (background layer) once
            ctx.drawImage(templateImg, 0, 0);

            // Helper: resolve slot coordinates in canvas pixels
            function resolveSlot(photoArea) {
                let x, y, w, h;
                if (
                    photoArea &&
                    photoArea.canvasWidth &&
                    photoArea.canvasHeight
                ) {
                    const ratioCenterX = photoArea.x / photoArea.canvasWidth;
                    const ratioCenterY = photoArea.y / photoArea.canvasHeight;
                    const ratioW = photoArea.width / photoArea.canvasWidth;
                    const ratioH = photoArea.height / photoArea.canvasHeight;
                    const centerX = ratioCenterX * canvas.width;
                    const centerY = ratioCenterY * canvas.height;
                    w = Math.round(ratioW * canvas.width);
                    h = Math.round(ratioH * canvas.height);
                    x = Math.round(centerX - w / 2);
                    y = Math.round(centerY - h / 2);
                } else if (photoArea) {
                    x = Math.round(photoArea.x * canvas.width);
                    y = Math.round(photoArea.y * canvas.height);
                    w = Math.round(photoArea.width * canvas.width);
                    h = Math.round(photoArea.height * canvas.height);
                } else {
                    x = Math.round(defaultArea.x * canvas.width);
                    y = Math.round(defaultArea.y * canvas.height);
                    w = Math.round(defaultArea.width * canvas.width);
                    h = Math.round(defaultArea.height * canvas.height);
                }
                return { x, y, w, h };
            }

            // Helper: load image from data URL
            function loadImage(src) {
                return new Promise((res, rej) => {
                    const img = new Image();
                    img.crossOrigin = "anonymous";
                    img.onload = () => res(img);
                    img.onerror = () => rej(new Error("Failed to load photo"));
                    img.src = src;
                });
            }

            // Draw each photo into its corresponding slot
            try {
                for (let i = 0; i < photos.length; i++) {
                    if (!photos[i]) continue;

                    const photoArea = photoSlots[i] ?? defaultArea ?? null;
                    if (!photoArea) continue;

                    const { x, y, w, h } = resolveSlot(photoArea);

                    if (w <= 0 || h <= 0) {
                        console.warn(`Invalid slot dimensions for slot ${i}:`, {
                            x,
                            y,
                            w,
                            h,
                        });
                        continue;
                    }

                    const slotX = Math.max(0, Math.min(x, canvas.width - 1));
                    const slotY = Math.max(0, Math.min(y, canvas.height - 1));
                    const slotW = Math.max(
                        1,
                        Math.min(w, canvas.width - slotX),
                    );
                    const slotH = Math.max(
                        1,
                        Math.min(h, canvas.height - slotY),
                    );

                    const photoImg = await loadImage(photos[i]);

                    const photoAspect = photoImg.width / photoImg.height;
                    const areaAspect = slotW / slotH;
                    let drawW = slotW,
                        drawH = slotH;
                    if (photoAspect > areaAspect) {
                        drawH = slotH;
                        drawW = slotH * photoAspect;
                    } else {
                        drawW = slotW;
                        drawH = slotW / photoAspect;
                    }

                    const drawX = slotX + (slotW - drawW) / 2;
                    const drawY = slotY + (slotH - drawH) / 2;

                    ctx.save();
                    ctx.beginPath();
                    ctx.rect(slotX, slotY, slotW, slotH);
                    ctx.clip();
                    if (mirror) {
                        ctx.drawImage(
                            photoImg,
                            0,
                            0,
                            photoImg.width,
                            photoImg.height,
                            drawX + drawW,
                            drawY,
                            -drawW,
                            drawH,
                        );
                    } else {
                        ctx.drawImage(photoImg, drawX, drawY, drawW, drawH);
                    }
                    ctx.restore();
                }

                resolve(canvas.toDataURL("image/png"));
            } catch (err) {
                reject(err);
            }
        };

        templateImg.onerror = () => reject(new Error("Failed to load frame"));
        templateImg.src = frameImageUrl;
    });
}
