<!-- cursor.php - Custom Cursor Component -->
<style>
    * { cursor: none !important; }
    ::selection {
    background: #f2eefb;
    color: #8B5CF6;
}
    .cursor-dot {
        width: 8px; height: 8px;
        background: linear-gradient(135deg, #C084FC, #8B5CF6);
        border-radius: 50%;
        position: fixed;
        pointer-events: none;
        z-index: 9999;
        transition: transform 0.1s ease, width 0.2s, height 0.2s;
        box-shadow: 0 0 12px rgba(192,132,252,0.8);
    }
    .cursor-ring {
        width: 32px; height: 32px;
        border: 2px solid rgba(192,132,252,0.7);
        border-radius: 50%;
        position: fixed;
        pointer-events: none;
        z-index: 9998;
        transition: transform 0.15s cubic-bezier(0.2, 0.9, 0.4, 1.1), width 0.3s, height 0.3s;
        backdrop-filter: blur(2px);
    }
    .cursor-dot.hover-grow { transform: scale(2.2); background: #e9d5ff; box-shadow: 0 0 25px #C084FC; }
    .cursor-ring.hover-ring { transform: scale(0.6); border-color: #e9d5ff; border-width: 3px; background: rgba(192,132,252,0.1); }
    .cursor-dot.click-effect { transform: scale(1.2); background: #fff; box-shadow: 0 0 20px #fff; }
    .cursor-ring.click-effect { transform: scale(1.3); border-color: #fff; }
    @media (max-width: 768px) { .cursor-dot, .cursor-ring { display: none; } * { cursor: auto; } }
</style>
<div class="cursor-dot"></div>
<div class="cursor-ring"></div>
<script>
(function(){
    if(window.innerWidth<=768)return;
    const d=document.querySelector('.cursor-dot'), r=document.querySelector('.cursor-ring');
    if(!d||!r)return;
    let mx=0,my=0,rx=0,ry=0;
    document.addEventListener('mousemove',(e)=>{mx=e.clientX;my=e.clientY;d.style.transform=`translate(${mx-4}px,${my-4}px)`;});
    function animate(){rx+=(mx-rx)*0.15;ry+=(my-ry)*0.15;r.style.transform=`translate(${rx-16}px,${ry-16}px)`;requestAnimationFrame(animate);}
    animate();
    const addHover=()=>{d.classList.add('hover-grow');r.classList.add('hover-ring');};
    const remHover=()=>{d.classList.remove('hover-grow');r.classList.remove('hover-ring');};
    const addClick=()=>{d.classList.add('click-effect');r.classList.add('click-effect');setTimeout(()=>{d.classList.remove('click-effect');r.classList.remove('click-effect');},150);};
    document.querySelectorAll('a,button,.btn,.feature-card,.glass-card,.pawn-card,.service-card,[href],input,select,.page-pill,.fab-main,.fab-action').forEach(el=>{el.addEventListener('mouseenter',addHover);el.addEventListener('mouseleave',remHover);});
    document.addEventListener('mousedown',addClick);
})();
</script>