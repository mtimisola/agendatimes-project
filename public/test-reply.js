document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ test-reply.js loaded!");

    const replyBtns = document.querySelectorAll(".reply-btn");
    const form = document.getElementById("commentForm");
    const replyIndicator = document.getElementById("replyIndicator");
    const cancelReplyBtn = document.getElementById("cancelReplyBtn");

    if (!form) {
        console.error("❌ Comment form not found!");
        return;
    }

    const parentInput = form.querySelector('input[name="parent_id"]');
    const commentTextarea = form.querySelector('textarea[name="comment"]');

    replyBtns.forEach(function (btn) {
        btn.addEventListener("click", function () {
            const parentId = this.getAttribute("data-id");

            parentInput.value = parentId;

            replyIndicator.textContent =
                "You are replying to a comment (ID: " + parentId + ")";
            replyIndicator.classList.remove("d-none");

            cancelReplyBtn.classList.remove("d-none");

            form.scrollIntoView({ behavior: "smooth" });
            commentTextarea.focus();

            console.log("➡️ Reply clicked, parent_id set to:", parentId);
        });
    });

    cancelReplyBtn.addEventListener("click", function () {
        parentInput.value = "";
        replyIndicator.textContent = "";
        replyIndicator.classList.add("d-none");
        cancelReplyBtn.classList.add("d-none");

        console.log("↩️ Reply cancelled.");
    });
});
