import './bootstrap';
import Lenis from 'lenis';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

window.Lenis = Lenis;
window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;
