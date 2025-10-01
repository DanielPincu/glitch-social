// scripts/tab-switcher.js
document.addEventListener("DOMContentLoaded", () => {
  const hotTabBtn = document.getElementById("hotTabBtn");
  const followingTabBtn = document.getElementById("followingTabBtn");
  const hotFeed = document.getElementById("hotFeed");
  const followingFeed = document.getElementById("followingFeed");

  function showHot() {
    hotFeed.classList.remove("hidden");
    followingFeed.classList.add("hidden");
    hotTabBtn.classList.add("bg-red-600", "text-white");
    hotTabBtn.classList.remove("bg-green-400");
    followingTabBtn.classList.remove("bg-red-600", "text-white", "bg-green-400");
  }

  function showFollowing() {
    followingFeed.classList.remove("hidden");
    hotFeed.classList.add("hidden");
    followingTabBtn.classList.add("bg-red-600", "text-white");
    followingTabBtn.classList.remove("bg-green-400");
    hotTabBtn.classList.remove("bg-red-600", "text-white", "bg-green-400");
  }

  hotTabBtn.addEventListener("click", showHot);
  followingTabBtn.addEventListener("click", showFollowing);

  // Show hot by default
  showHot();
});