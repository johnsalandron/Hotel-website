document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    const id = params.get("id");

    if (!id) {
        alert("Room ID not found in URL.");
        return;
    }

    fetch(`php/get_room_detail.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            // Populate room details
            document.getElementById("room-title").textContent = data.room_name;
            document.getElementById("room-price").textContent = data.price;
            document.getElementById("room-beds").textContent = data.beds;
            document.getElementById("room-description").textContent = data.description;

            const mainImage = document.getElementById("mainImage");
            const thumbnailContainer = document.getElementById("thumbnailContainer");

            const pictures = data.room_pictures || [];
            const maxSlots = 3;

            // Set main image
            mainImage.src = pictures[0] || "";

            // Clear old thumbnails (if any)
            thumbnailContainer.innerHTML = "";

            for (let i = 0; i < maxSlots; i++) {
                const imgSrc = pictures[i];
                const thumb = document.createElement("div");

                // Base styles
                thumb.style.width = "100%";
                thumb.style.aspectRatio = "1/1";
                thumb.style.overflow = "hidden";
                thumb.style.borderRadius = "8px";
                thumb.style.border = "2px solid transparent";
                thumb.style.cursor = "pointer";
                thumb.classList.add("thumb");

                if (imgSrc) {
                    const img = document.createElement("img");
                    img.src = imgSrc;
                    img.className = "img-fluid w-100 h-100";
                    img.style.objectFit = "cover";

                    thumb.appendChild(img);

                    thumb.addEventListener("click", () => {
                        mainImage.src = imgSrc;
                        document.querySelectorAll("#thumbnailContainer .thumb").forEach(div => {
                            div.style.border = "2px solid transparent";
                        });
                        thumb.style.border = "2px solid #007bff";
                    });

                    if (i === 0) {
                        thumb.style.border = "2px solid #007bff";
                    }
                } else {
                    thumb.style.backgroundColor = "#ddd"; // gray placeholder
                    // No img or alt here
                }

                thumbnailContainer.appendChild(thumb);
            }
        })
        .catch(err => {
            console.error("Error loading room:", err);
        });
});
