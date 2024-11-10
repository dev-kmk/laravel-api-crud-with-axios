<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laravel Api Axios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
    <div class="container py-4 py-md-5">
        <div class="row flex-column-reverse flex-md-row row-gap-4">
            <div class="col-md-8">
                <h4 class="mb-3">Post List</h4>
                <div id="infoAlert"></div>
                <div id="post-wrapper"></div>
            </div>
            <div class="col-md-4">
                <h5 class="mb-3">Create Post</h5>
                <div id="successAlert"></div>
                <form name="createPostForm">
                    <div class="form-group mb-3">
                        <label for="title">Title</label>
                        <input type="text" name="title" class="form-control mt-1">
                        <i id="inputError" class="text-danger"></i>
                    </div>
                    
                    <div class="form-group mb-4">
                        <label for="title">Description</label>
                        <textarea name="description" rows="5" class="form-control mt-1"></textarea>
                        <i id="desError" class="text-danger"></i>
                    </div>
                    
                    <button class="btn btn-primary w-100">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h1 class="modal-title fs-5" id="editModalLabel">Edit Post</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form name="editPostForm">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="title">Title</label>
                        <input type="text" name="title" class="form-control mt-1">
                        <i id="editInputError" class="text-danger"></i>
                    </div>
                    
                    <div class="form-group mb-4">
                        <label for="title">Description</label>
                        <textarea name="description" rows="5" class="form-control mt-1"></textarea>
                        <i id="editDesError" class="text-danger"></i>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
            
        </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    

    <script>
        const postWrapper = document.querySelector("#post-wrapper");

        // READ
        axios.get('/api/posts')
        .then(response => {
            response.data.forEach(data => showPost(data));
        })
        .catch(error => {
            console.log(error);
            if(error.response.status == 404){
                console.log(`url - "${error.response.config.url}" not found!`);
            }
        });

        // CREATE
        const createPostForm = document.forms['createPostForm'];
        const titleInput = createPostForm['title'];
        const descriptionInput = createPostForm['description'];
        createPostForm.onsubmit = (e) => {
            e.preventDefault();

            axios.post('/api/posts', {
                'title': titleInput.value,
                'description': descriptionInput.value
            })
            .then(response => {
                console.log(response);
                showPost(response.data.post);
                
                createPostForm.reset();

                document.querySelector("#inputError").innerHTML = "";
                document.querySelector("#desError").innerHTML = "";

                document.querySelector('#successAlert').innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    ${response.data.msg}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                `;
            })
            .catch(err => {
                const inputErr = err.response.data.errors.title;
                const desErr = err.response.data.errors.description;

                document.querySelector("#inputError").innerHTML = inputErr ? inputErr : "";
                document.querySelector("#desError").innerHTML = desErr ? desErr : "";
            });
        }

        // Edit & UPDATE
        const editPostForm = document.forms["editPostForm"];
        const editTitleInput = editPostForm['title'];
        const editDesInput = editPostForm['description'];
        const editTitleErr = document.querySelector("#editInputError");
        const editDesError = document.querySelector("#editDesError");

        var getPostID;

        function editPost(postID){
            getPostID = postID;

            axios.get(`api/posts/${postID}`)
            .then(response => {
                editTitleInput.value = response.data.title;
                editDesInput.value = response.data.description;
            })
            .catch(err => {
                console.log(err);
            });
        }

        editPostForm.onsubmit = function(e){
            e.preventDefault();

            if(editTitleInput.value == ""){
                editTitleErr.innerHTML = "Title Field Required";
                editDesError.innerHTML = "";
            }else if(editDesInput.value == ""){
                editDesError.innerHTML = "Description Field Required";
                editTitleErr.innerHTML = "";
            }else{
                editDesError.innerHTML = "";

                axios.put(`api/posts/${getPostID}`, {
                    'title': editTitleInput.value,
                    'description': editDesInput.value
                })
                .then(response => {
                    $('#editModal').modal('hide');

                    document.querySelector("#infoAlert").innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        ${response.data.msg}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    `;

                    // Add Update Data To Edited Post
                    const posts = postWrapper.querySelectorAll(".card");
                    posts.forEach(post => {
                        const loopPostId = post.getAttribute("id");
                        if(loopPostId == getPostID){
                            post.querySelector(".card-title").innerHTML = editTitleInput.value;
                            post.querySelector(".card-text").innerHTML = editDesInput.value;
                        }
                    });
                })
                .catch(err => {
                    console.log(err);
                });
            }
        }

        // DELETE
        function deletePost(postID){
            if(confirm("Are you sure you want to delete?")){
                axios.delete(`api/posts/${postID}`)
                .then(response => {

                    document.querySelector("#infoAlert").innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${response.data.msg}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        `;

                    // Remove Deleted Post
                    const posts = postWrapper.querySelectorAll(".card");
                    posts.forEach(post => {
                        const loopPostId = post.getAttribute("id");
                        if(loopPostId == postID){
                            post.style.display = "none";
                        }
                    });
                })
                .catch(err => {
                    console.log(err);
                }); 
            }
        }

        function showPost(data){
            postWrapper.innerHTML += `
                <div class="card border-0 shadow-sm my-4" id="${data.id}">
                    <div class="card-body">
                        <h5 class="card-title">${data.title}</h5> 
                        <p class="card-text">${data.description}</p>
                        <div class="text-end">
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" onclick="editPost(${data.id})">Edit</button>
                            <button class="btn btn-danger btn-sm" onclick="deletePost(${data.id})">Delete</button>
                        </div>
                    </div>
                </div>
            `;
        }
        
    </script>
</body>
</html>