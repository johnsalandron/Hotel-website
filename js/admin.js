let rooms = [], toUpload = [];
let priceChart;

function loadRooms() {
    fetch('php/room_crud.php')
        .then(res => res.json())
        .then(data => {
            rooms = data.rooms;
            document.getElementById('totalRooms').textContent = data.total_rooms;

            const tbody = document.getElementById('room-table');
            tbody.innerHTML = '';

            rooms.forEach(r => {
                const pics = r.room_pictures || [];
                const slots = pics.map(url => {
                    return `<img src="${url}" class="thumb" onerror="this.style.display='none'">`;
                });

                const placeholders = Array(4 - slots.length).fill('<div class="thumb-placeholder"></div>');
                const thumbsHTML = [...slots, ...placeholders].join('');

                const row = document.createElement('tr');
                row.innerHTML = `
                  <td>${r.id}</td>
                  <td style="display:flex;">${thumbsHTML}</td>
                  <td>${r.room_name}</td>
                  <td>₱${r.price}</td>
                  <td>${r.beds}</td>
                  <td>${r.description}</td>
                  <td>
                    <button class="btn btn-sm btn-primary edit-btn" data-id="${r.id}">Edit</button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="${r.id}">Delete</button>
                  </td>
                `;

                row.querySelector('.edit-btn').addEventListener('click', () => openModal(parseInt(r.id)));
                row.querySelector('.delete-btn').addEventListener('click', () => deleteRoom(r.id));

                tbody.appendChild(row);
            });

            // ----- Chart rendering -----
            const ctx = document.getElementById('priceChart')?.getContext('2d');
            if (ctx) {
                const topRooms = [...rooms].sort((a, b) => b.price - a.price).slice(0, 5);
                const labels = topRooms.map(r => r.room_name);
                const dataPrices = topRooms.map(r => r.price);

                if (priceChart) priceChart.destroy();

                priceChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Top 5 Room Prices',
                            data: dataPrices,
                            backgroundColor: 'rgba(75, 192, 192, 0.7)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        })
        .catch(err => console.error("Error loading rooms:", err));
}


function deleteRoom(id) {
    if (!confirm('Are you sure you want to delete this room?')) return;

    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);

    fetch('php/room_crud.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(() => loadRooms())
        .catch(err => console.error("Error deleting room:", err));
}

function openModal(id) {
    resetModal();

    if (id !== undefined) {
        const room = rooms.find(r => parseInt(r.id) === parseInt(id));
        if (!room) return;

        document.getElementById('roomId').value = room.id;
        document.getElementById('roomName').value = room.room_name;
        document.getElementById('roomPrice').value = room.price;
        document.getElementById('roomBeds').value = room.beds;
        document.getElementById('roomDesc').value = room.description;

        (room.room_pictures || []).forEach(url => addExistingThumbnail(url));

        document.getElementById('modalTitle').textContent = 'Edit Room';
    } else {
        document.getElementById('modalTitle').textContent = 'Add Room';
    }

    new bootstrap.Modal(document.getElementById('roomModal')).show();
}

function resetModal() {
    document.getElementById('roomForm').reset();
    toUpload = [];
    document.getElementById('imgUploader').innerHTML = '';
    renderUploader();
}

function renderUploader() {
    const div = document.getElementById('imgUploader');
    div.innerHTML = '';
    [...toUpload, ...Array(4 - toUpload.length)].forEach((img, i) => {
        const slot = document.createElement('div');
        slot.className = 'img-slot';
        if (img) {
            const thumb = document.createElement('img');
            thumb.src = img.url;
            slot.appendChild(thumb);
            const btn = document.createElement('button');
            btn.className = 'remove';
            btn.innerHTML = '&times;';
            btn.onclick = e => {
                e.stopPropagation();
                toUpload.splice(i, 1);
                renderUploader();
            };
            slot.appendChild(btn);
        } else {
            slot.textContent = '+';
            slot.onclick = () => addImage(i);
        }
        div.appendChild(slot);
    });
}

function addExistingThumbnail(src) {
    if (toUpload.length < 4) {
        toUpload.push({ url: src });
        renderUploader();
    }
}

function addImage(idx) {
    if (toUpload.length >= 4) return;
    const inp = document.createElement('input');
    inp.type = 'file';
    inp.accept = 'image/*';
    inp.onchange = () => {
        const file = inp.files[0];
        const reader = new FileReader();
        reader.onload = () => {
            toUpload.splice(idx, 0, { file, url: reader.result });
            renderUploader();
        };
        reader.readAsDataURL(file);
    };
    inp.click();
}

document.getElementById('roomForm').addEventListener('submit', e => {
    e.preventDefault();
    const fd = new FormData();
    const isUpdate = document.getElementById('roomId').value;

    fd.append('action', isUpdate ? 'update' : 'create');
    if (isUpdate) fd.append('id', document.getElementById('roomId').value);

    fd.append('room_name', document.getElementById('roomName').value);
    fd.append('price', document.getElementById('roomPrice').value);
    fd.append('beds', document.getElementById('roomBeds').value);
    fd.append('description', document.getElementById('roomDesc').value);

    const jsonPics = toUpload.map(x => (
        x.file ? '' : x.url // full path already set like "img/room-1.jpg"
    )).filter(x => x); // remove empty

    fd.append('room_pictures_json', JSON.stringify(jsonPics));
    toUpload.forEach(x => x.file && fd.append('images[]', x.file));

    fetch('php/room_crud.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(() => {
            loadRooms();
            bootstrap.Modal.getInstance(document.getElementById('roomModal')).hide();
        })
        .catch(err => console.error("Error saving room:", err));
});

loadRooms();
