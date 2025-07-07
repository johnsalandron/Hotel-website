document.addEventListener("DOMContentLoaded", () => {
    loadRooms(1);

    function loadRooms(page) {
        fetch(`php/get_paginated_rooms.php?page=${page}`)
            .then(res => res.json())
            .then(data => {
                renderRooms(data.rooms);
                renderPagination(data.total_pages, data.current_page);
            });
    }

    function renderRooms(rooms) {
        const container = document.getElementById("room-container");
        container.innerHTML = "";

        rooms.forEach(room => {
            const images = JSON.parse(room.room_pictures || "[]");
            const firstImage = images.length ? images[0] : "img/default.jpg";

            const card = `
                <div class="col-lg-4 col-md-6">
                    <div class="room-item shadow rounded overflow-hidden">
                        <div class="position-relative">
                            <img class="img-fluid object-fit-cover contain w-100" style="height: 16rem;" src="${firstImage}" alt="">
                            <small class="position-absolute start-0 top-100 translate-middle-y bg-primary text-white rounded py-1 px-3 ms-4">
                                $${room.price}/Night
                            </small>
                        </div>
                        <div class="p-4 mt-2">
                            <div class="d-flex justify-content-between mb-3">
                                <h5 class="mb-0">${room.room_name}</h5>
                                <div>
                                    ${'<small class="fa fa-star text-primary"></small>'.repeat(5)}
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <small class="border-end me-3 pe-3">
                                    <i class="fa fa-bed text-primary me-2"></i>${room.beds} Bed
                                </small>
                                <small><i class="fa fa-wifi text-primary me-2"></i>Wifi</small>
                            </div>
                            <p class="text-body mb-3 text-truncate-multiline">${room.description.substring(0, 100)}...</p>
                            <div class="d-flex justify-content-between">
                                <a class="btn btn-sm btn-primary rounded py-2 px-4" href="room_detail.html?id=${room.id}">View Detail</a>
                                <a class="btn btn-sm btn-dark rounded py-2 px-4" href="#">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += card;
        });
    }

    function renderPagination(totalPages, currentPage) {
        const container = document.getElementById("pagination-container");
        container.innerHTML = "";

        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement("li");
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener("click", e => {
                e.preventDefault();
                loadRooms(i);
            });
            container.appendChild(li);
        }
    }
});
