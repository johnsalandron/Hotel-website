document.addEventListener("DOMContentLoaded", () => {
    fetch('php/get_rooms.php')
        .then(res => res.json())
        .then(data => {
            const container = document.querySelector("#room-container");
            container.innerHTML = ""; // Clear previous content if any

            data.forEach(room => {
                const stars = '<small class="fa fa-star text-primary"></small>'.repeat(5);
                const html = `
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="room-item shadow rounded overflow-hidden">
                            <div class="position-relative">
                                <img class="img-fluid object-fit-cover contain w-100" style="height: 16rem;" src="${room.room_pictures[0]}" alt="">
                                <small class="position-absolute start-0 top-100 translate-middle-y bg-primary text-white rounded py-1 px-3 ms-4">$${room.price}/Night</small>
                            </div>
                            <div class="p-4 mt-2">
                                <div class="d-flex justify-content-between mb-3">
                                    <h5 class="mb-0">${room.room_name}</h5>
                                    <div class="ps-2">${stars}</div>
                                </div>
                                <div class="d-flex mb-3">
                                    <small class="border-end me-3 pe-3"><i class="fa fa-bed text-primary me-2"></i>${room.beds} Bed</small>
                                    <small><i class="fa fa-wifi text-primary me-2"></i>Wifi</small>
                                </div>
                                <p class="text-body mb-3 text-truncate-multiline">${room.description}</p>
                                <div class="d-flex justify-content-between">
                                    <a class="btn btn-sm btn-primary rounded py-2 px-4" href="room_detail.html?id=${room.id}">View Detail</a>
                                    <a class="btn btn-sm btn-dark rounded py-2 px-4" href="#">Book Now</a>
                                </div>
                            </div>
                        </div>
                    </div>`;
                container.insertAdjacentHTML("beforeend", html);
            });
        })
        .catch(err => {
            console.error("Error loading rooms:", err);
        });
});
