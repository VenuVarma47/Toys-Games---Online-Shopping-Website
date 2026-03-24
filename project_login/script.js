// users and passcodes this section will have all the details of users and their passcode
const validUsers = {
    "Yuvraj": "YuvrajGupta@930",
    "admin":"password",
    "yuvvya":"yuvraj gupta",
    "Shubham": "shubham_from_champaran",
    "Peeyush": "peeyush_from_agra"
  };
  
  // Get login page elements. This part will fetch the details from login page as username and password
  const loginForm = document.getElementById("loginForm");
  const errorMsg = document.getElementById("errorMsg");
  
  // Get upload page elements
  const userSpan = document.getElementById("userSpan");
  const fileInput = document.getElementById("fileInput");
  const uploadBtn = document.getElementById("uploadBtn");
  const downloadBtn = document.getElementById("downloadBtn");
  const recentFilesList = document.getElementById("recentFilesList");
  
  // Popup elements
  const popup = document.getElementById("popup");
  const popupMessage = document.getElementById("popupMessage");
  const closePopup = document.getElementById("closePopup");
  const loader = document.getElementById("loader");
  const progressElem = document.querySelector(".progress");
  
  // Recent files array (simulate persistent recent files)
  let recentFiles = [];
  
  
  // LOGIN PAGE LOGIC
  // ------------------------
  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const username = document.getElementById("username").value.trim();
      const password = document.getElementById("password").value.trim();
      
      // Validate username and password
      if (validUsers[username] && validUsers[username] === password) {
        // Save username and redirect to upload page
        sessionStorage.setItem("loggedInUser", username);
        window.location.href = "index.html";
      } else {
        errorMsg.textContent = "Invalid username or password.";
      }
    });
  }
  
  // ------------------------
  // UPLOAD PAGE LOGIC
  // ------------------------
  
  // Helper function to update the Recent Files list in the DOM

  
  // Simulate progress loader before showing popup

  
  // UPLOAD BUTTON LOGIC
 
  
  // DOWNLOAD BUTTON LOGIC
 
  
  // POPUP LOGIC: Close the popup when the close button is clicked
  if (closePopup) {
    closePopup.addEventListener("click", () => {
      popup.style.display = "none";
    });
  }