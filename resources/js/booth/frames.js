/**
 * Frame selection logic.
 */

export function initFrames(stateMachine, session) {
    const frames = JSON.parse(document.body.dataset.frames || "[]");
    let selectedFrameId = null;

    const btnBack = document.getElementById("btn-frame-back");
    const btnNext = document.getElementById("btn-frame-next");
    const grid = document.getElementById("frame-grid");

    if (!grid) return;

    function selectFrame(frameId) {
        selectedFrameId = frameId;
        grid.querySelectorAll(".frame-card").forEach((btn) => {
            const id = String(btn.dataset.frameId);
            if (id === String(frameId)) {
                btn.classList.add(
                    "border-blue-600",
                    "bg-gray-200",
                    "scale-105",
                    "transition",
                );
                btn.classList.remove("border-transparent");
            } else {
                btn.classList.remove(
                    "border-blue-600",
                    "bg-gray-200",
                    "scale-105",
                );
                btn.classList.add("border-transparent");
            }
        });
        btnNext.disabled = !selectedFrameId;
        btnNext.classList.toggle("cursor-not-allowed", !selectedFrameId);
        btnNext.classList.toggle("opacity-50", !selectedFrameId);
        if (selectedFrameId) {
            btnNext.classList.remove("bg-gray-200", "text-gray-500");
            btnNext.classList.add(
                "bg-blue-600",
                "text-white",
                "cursor-pointer",
            );
        } else {
            btnNext.classList.add("bg-gray-200", "text-gray-500");
            btnNext.classList.remove(
                "bg-blue-600",
                "text-white",
                "cursor-pointer",
            );
        }
    }

    grid.querySelectorAll(".frame-card").forEach((btn) => {
        btn.addEventListener("click", () => selectFrame(btn.dataset.frameId));
    });

    btnBack?.addEventListener("click", () =>
        stateMachine.setState(stateMachine.STATES.IDLE),
    );
    btnNext?.addEventListener("click", () => {
        if (!selectedFrameId) return;

        // Loading state: disable button & show spinner
        btnNext.disabled = true;
        btnNext.classList.add("opacity-75", "cursor-not-allowed");
        btnNext.classList.remove("cursor-pointer");
        btnNext.innerHTML = `
            <svg class="animate-spin h-4 w-4 inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Bentar
        `;

        session
            .saveFrame(selectedFrameId)
            .then(() => {
                btnNext.disabled = false;
                btnNext.innerHTML = "Lanjut";
                btnNext.classList.remove("opacity-75", "cursor-not-allowed");
                btnNext.classList.add("cursor-pointer");
                stateMachine.setState(stateMachine.STATES.REVIEW_ORDER);
            })
            .catch(() => {
                // Restore button if error
                btnNext.disabled = false;
                btnNext.innerHTML = "Lanjut";
                btnNext.classList.remove("opacity-75", "cursor-not-allowed");
                btnNext.classList.add("cursor-pointer");
            });
    });

    return {
        getSelectedFrame: () => selectedFrameId,
        getFrames: () => frames,
    };
}
