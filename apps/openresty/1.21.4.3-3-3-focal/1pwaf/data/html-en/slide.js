window.onload = function () {
    (function () {
        const dragContainer = document.getElementById("dragContainer");
        const dragBg = document.getElementById("dragBg");
        const dragText = document.getElementById("dragText");
        const dragHandler = document.getElementById("dragHandler");
        
        let maxHandleOffset;
        
        let isVertifySucc = false;
        
        function initDrag() {
            maxHandleOffset = dragContainer.clientWidth - dragHandler.clientWidth;
            dragText.textContent = "Slide to verify";
            dragHandler.className = "dragHandlerDefault";
            dragHandler.style.left = "0px";
            dragBg.style.width = "0px";
            
            dragHandler.addEventListener("mousedown", onDragStart);
            dragHandler.addEventListener("touchstart", onDragStart);
        }
        
        function onDragStart(e) {
            e.preventDefault();
            if (isVertifySucc) return;
            
            if (e.type === "mousedown" || (e.type === "touchstart" && e.touches.length === 1)) {
                maxHandleOffset = dragContainer.clientWidth - dragHandler.clientWidth;
                
                document.addEventListener("mousemove", onDragMove);
                document.addEventListener("touchmove", onDragMove);
                document.addEventListener("mouseup", onDragEnd);
                document.addEventListener("touchend", onDragEnd);
            }
        }
        
        function onDragMove(e) {
            let clientX;
            if (e.type === "mousemove") {
                clientX = e.clientX;
            } else if (e.type === "touchmove" && e.touches.length === 1) {
                clientX = e.touches[0].clientX;
            } else {
                return;
            }
            
            let containerOffsetX = clientX - dragContainer.getBoundingClientRect().left;
            let left = containerOffsetX - dragHandler.clientWidth / 2;
            
            if (left < 0) {
                left = 0;
            } else if (left > maxHandleOffset) {
                left = maxHandleOffset;
            }
            
            dragHandler.style.left = left + "px";
            dragBg.style.width = left + dragHandler.clientWidth / 2 + "px";
        }
        
        function onDragEnd() {
            document.removeEventListener("mousemove", onDragMove);
            document.removeEventListener("touchmove", onDragMove);
            document.removeEventListener("mouseup", onDragEnd);
            document.removeEventListener("touchend", onDragEnd);
            
            if (!isVertifySucc) {
                let left = dragHandler.offsetLeft;
                
                if (left >= maxHandleOffset - 1) {
                    verifySucc();
                } else {
                    dragHandler.style.left = "0px";
                    dragBg.style.width = "0px";
                }
            }
        }
        
        function verifySucc() {
            isVertifySucc = true;
            dragText.textContent = "Verification Passed";
            dragHandler.className = "dragHandlerSuccess";
            
            dragHandler.style.left = maxHandleOffset + "px";
            dragBg.style.width = dragContainer.clientWidth + "px";
            
            dragHandler.removeEventListener("mousedown", onDragStart);
            dragHandler.removeEventListener("touchstart", onDragStart);
            
            let xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    window.location.reload();
                }
            };
            const requestUrl = "%s-%s-%s-%s-%s-";
            xhr.open("GET", requestUrl, true);
            xhr.send();
        }
        
        window.addEventListener('resize', initDrag);
        
        initDrag();
    })();
};
