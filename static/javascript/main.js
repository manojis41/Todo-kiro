document.addEventListener("DOMContentLoaded", function () {
  openedEditBox = null;
  previousTask = null;
  editedTask = null;

  document
    .querySelectorAll(
      `button[name="edit"],button[name="remove"],input[type="checkbox"]`
    )
    .forEach(function (button) {
      button.addEventListener("click", function () {
        currentTask = this.parentElement.parentElement;
        id = currentTask.children[1].id;
        type = currentTask.children[1].type.value;
        buttonType = button.getAttribute("name") || button.getAttribute("type");

        function removeEditBoxAndShowTask() {
          if (openedEditBox) {
            openedEditBox.remove();
          }
          if (previousTask !== null) {
            if (previousTask.style.display == "none") {
              previousTask.style.display = "flex";
              previousTask = null;
            }
          }
        }
        if (buttonType == "edit") {
          removeEditBoxAndShowTask();
          if (previousTask !== currentTask) {
            currentTask.style.display = "none";
            editedTask = document.createElement("div");
            editedTask.innerHTML = `<form method="POST" action="/todo/okay" class = "task" id = "updateForm">
    <input type="text" name="updatedTask" placeholder="      Update your task here ......." required />
    <input value="${id}" name="boxid" hidden />
    <input value="${type}" name="type" hidden />
   <button type="submit"><i class="fa-regular fa-floppy-disk" ></i></button>
    <button name="cancel" type="reset" ><i class="fa-regular fa-rectangle-xmark"></i></button>

      </form>`;

            currentTask.after(editedTask);

            cancleButton = document.querySelector(`button[name="cancel"`);
            if (
              cancleButton.addEventListener("click", removeEditBoxAndShowTask)
            ) {
              //the previousTask will be set to null automatically in the function
            } else {
              previousTask = currentTask;
            }
          }
          openedEditBox = document.getElementById("updateForm");
        }
        if (buttonType == "remove") {
          if (confirm("Are you sure want to delete this task.")) {
            form = document.getElementById(id);
            form.action = "/todo/remove";
            form.submit();
          }
        }
        if (buttonType == "checkbox") {
          form = document.getElementById(id);
          console.log(form.type.value);
          if (form.type.value == "current") form.action = "/todo/done";
          if (form.type.value == "completed") form.action = "/todo/ongoing";

          form.submit();
        }
      });
    });
});
