

    const canvas = document.getElementById("canvas");
    const ctx = canvas.getContext("2d");
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const fireworks = [];
    const particles = [];

    class Firework {
      constructor() {
        this.x = Math.random() * canvas.width;
        this.y = canvas.height;
        this.targetY = Math.random() * canvas.height / 2;
        this.speed = 10;
        this.color = `hsl(${Math.random() * 360}, 100%, 50%)`;
      }

      update() {
        this.y -= this.speed;
        if (this.y <= this.targetY) {
          this.explode();
          return true;
        }
        this.draw();
        return false;
      }

      draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, 2, 0, Math.PI * 2);
        ctx.fillStyle = this.color;
        ctx.fill();
      }

      explode() {
        for (let i = 0; i < 15; i++) {
          particles.push(new Particle(this.x, this.y, this.color));
        }
      }
    }

    class Particle {
      constructor(x, y, color) {
        this.x = x;
        this.y = y;
        this.speed = Math.random() * 3 + 1;
        this.angle = Math.random() * Math.PI * 2;
        this.color = color;
        this.alpha = 1;
      }

      update() {
        this.x += Math.cos(this.angle) * this.speed;
        this.y += Math.sin(this.angle) * this.speed;
        this.alpha -= 0.02;
        this.draw();
        return this.alpha <= 0;
      }

      draw() {
        ctx.globalAlpha = this.alpha;
        ctx.beginPath();
        ctx.arc(this.x, this.y, 2, 0, Math.PI * 2);
        ctx.fillStyle = this.color;
        ctx.fill();
        ctx.globalAlpha = 1;
      }
    }

    function animate() {
      // Clear canvas with full transparency (no background fill)
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      // Randomly launch fireworks
      if (Math.random() < 0.03) {
        fireworks.push(new Firework());
      }

      // Update fireworks, remove exploded ones
      for (let i = fireworks.length - 1; i >= 0; i--) {
        if (fireworks[i].update()) {
          fireworks.splice(i, 1);
        }
      }

      // Update particles, remove faded ones
      for (let i = particles.length - 1; i >= 0; i--) {
        if (particles[i].update()) {
          particles.splice(i, 1);
        }
      }

      requestAnimationFrame(animate);
    }

    animate();

    // Resize canvas on window resize
    window.addEventListener("resize", () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    });