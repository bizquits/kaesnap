/**
 * frame-editor.js
 * Alpine.js components untuk frame editor photobooth.
 * - zoomWorkspace : zoom in/out/fit
 * - frameCanvas   : drag & resize slots dengan clamp ke batas canvas
 */

document.addEventListener("alpine:init", () => {
    // ─── Zoom Workspace ───────────────────────────────────────────────────────
    Alpine.data("zoomWorkspace", (baseWidth, baseHeight) => ({
        zoom: 0.3,
        minZoom: 0.1,
        maxZoom: 2,
        step: 0.1,
        baseWidth,
        baseHeight,

        zoomIn() {
            this.zoom = Math.min(
                this.maxZoom,
                Math.round((this.zoom + this.step) * 100) / 100,
            );
        },
        zoomOut() {
            this.zoom = Math.max(
                this.minZoom,
                Math.round((this.zoom - this.step) * 100) / 100,
            );
        },
        reset() {
            this.zoom = 1;
        },

        fit() {
            this.$nextTick(() => {
                const ws = this.$refs.workspace;
                const paper = this.$refs.paper;
                if (!ws || !paper) return;

                const wsRect = ws.getBoundingClientRect();
                const bw = paper.getBoundingClientRect().width / this.zoom;
                const bh = paper.getBoundingClientRect().height / this.zoom;

                const next = Math.min(
                    (wsRect.width - 32) / bw,
                    (wsRect.height - 80) / bh,
                );
                this.zoom = Math.max(
                    this.minZoom,
                    Math.min(this.maxZoom, Math.round(next * 100) / 100),
                );
            });
        },
    }));

    // ─── Frame Canvas ─────────────────────────────────────────────────────────
    Alpine.data("frameCanvas", (canvasWidth, canvasHeight) => ({
        canvasWidth,
        canvasHeight,
        canvasEl: null,

        _dragId: null,
        _dragOffX: 0,
        _dragOffY: 0,
        _dragCurW: 0,
        _dragCurH: 0,

        _resizeId: null,
        _resizeHandle: "",
        _rsMouseX: 0,
        _rsMouseY: 0,
        _rsSlot: null,

        init() {
            this.canvasEl = this.$el;
        },

        // ── helpers ──────────────────────────────────────────────────────────

        _getScale() {
            const r = this.canvasEl.getBoundingClientRect();
            return {
                sx: canvasWidth / r.width,
                sy: canvasHeight / r.height,
                r,
            };
        },

        _getSlotEl(id) {
            return this.canvasEl.querySelector(`[data-slot-id="${id}"]`);
        },

        _readSlotData(el) {
            return {
                x: parseFloat(el.dataset.slotX) || 0,
                y: parseFloat(el.dataset.slotY) || 0,
                w: parseFloat(el.dataset.slotW) || 400,
                h: parseFloat(el.dataset.slotH) || 300,
            };
        },

        _applySlotDOM(el, x, y, w, h) {
            el.style.left = (x / canvasWidth) * 100 + "%";
            el.style.top = (y / canvasHeight) * 100 + "%";
            el.style.width = (w / canvasWidth) * 100 + "%";
            el.style.height = (h / canvasHeight) * 100 + "%";
            el.dataset.slotX = Math.round(x);
            el.dataset.slotY = Math.round(y);
            el.dataset.slotW = Math.round(w);
            el.dataset.slotH = Math.round(h);
        },

        _commitToWire(id) {
            const el = this._getSlotEl(id);
            if (!el) return;
            const d = this._readSlotData(el);
            this.$wire.call(
                "updateSlotPosition",
                id,
                Math.round(d.x),
                Math.round(d.y),
                Math.round(d.w),
                Math.round(d.h),
            );
        },

        // ── Drag ─────────────────────────────────────────────────────────────

        startDrag(e, id, slotX, slotY, slotW, slotH) {
            if (e.button !== 0) return;
            e.preventDefault();
            e.stopPropagation();

            const { sx, sy, r } = this._getScale();
            this._dragId = id;
            this._dragOffX = (e.clientX - r.left) * sx - slotX;
            this._dragOffY = (e.clientY - r.top) * sy - slotY;
            this._dragCurW = slotW;
            this._dragCurH = slotH;

            const onMove = (ev) => this._onDragMove(ev);
            const onUp = () => {
                this._commitToWire(this._dragId);
                this._dragId = null;
                document.removeEventListener("mousemove", onMove);
                document.removeEventListener("mouseup", onUp);
            };
            document.addEventListener("mousemove", onMove);
            document.addEventListener("mouseup", onUp);
        },

        _onDragMove(e) {
            if (this._dragId === null || !this.canvasEl) return;
            const { sx, sy, r } = this._getScale();

            let x = (e.clientX - r.left) * sx - this._dragOffX;
            let y = (e.clientY - r.top) * sy - this._dragOffY;

            // Clamp agar slot tidak keluar canvas
            x = Math.max(0, Math.min(canvasWidth - this._dragCurW, x));
            y = Math.max(0, Math.min(canvasHeight - this._dragCurH, y));

            const el = this._getSlotEl(this._dragId);
            if (el)
                this._applySlotDOM(el, x, y, this._dragCurW, this._dragCurH);
        },

        // ── Resize ───────────────────────────────────────────────────────────

        /**
         * handle: 'n' | 's' | 'e' | 'w' | 'nw' | 'ne' | 'sw' | 'se'
         */
        startResize(e, id, handle, slotX, slotY, slotW, slotH) {
            if (e.button !== 0) return;
            e.preventDefault();
            e.stopPropagation();

            this._resizeId = id;
            this._resizeHandle = handle;
            this._rsMouseX = e.clientX;
            this._rsMouseY = e.clientY;
            this._rsSlot = { x: slotX, y: slotY, w: slotW, h: slotH };

            const onMove = (ev) => this._onResizeMove(ev);
            const onUp = () => {
                this._commitToWire(this._resizeId);
                this._resizeId = null;
                document.removeEventListener("mousemove", onMove);
                document.removeEventListener("mouseup", onUp);
            };
            document.addEventListener("mousemove", onMove);
            document.addEventListener("mouseup", onUp);
        },

        _onResizeMove(e) {
            if (this._resizeId === null || !this.canvasEl) return;
            const { sx, sy } = this._getScale();

            const dx = (e.clientX - this._rsMouseX) * sx;
            const dy = (e.clientY - this._rsMouseY) * sy;
            const s = this._rsSlot;
            const MIN = 40;
            let { x, y, w, h } = { x: s.x, y: s.y, w: s.w, h: s.h };
            const hnd = this._resizeHandle;

            if (hnd.includes("e")) {
                w = Math.max(MIN, s.w + dx);
            }
            if (hnd.includes("w")) {
                const nw = Math.max(MIN, s.w - dx);
                x = s.x + s.w - nw;
                w = nw;
            }
            if (hnd.includes("s")) {
                h = Math.max(MIN, s.h + dy);
            }
            if (hnd.includes("n")) {
                const nh = Math.max(MIN, s.h - dy);
                y = s.y + s.h - nh;
                h = nh;
            }

            // Clamp
            x = Math.max(0, Math.min(canvasWidth - MIN, x));
            y = Math.max(0, Math.min(canvasHeight - MIN, y));
            w = Math.min(w, canvasWidth - x);
            h = Math.min(h, canvasHeight - y);

            const el = this._getSlotEl(this._resizeId);
            if (el) this._applySlotDOM(el, x, y, w, h);
        },
    }));
});
