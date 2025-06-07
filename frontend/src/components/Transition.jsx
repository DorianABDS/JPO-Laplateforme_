import React, { useContext } from 'react';
import { SwitchTransition, Transition } from 'react-transition-group';
import { useLocation } from 'react-router-dom';
import gsap from 'gsap';

import TransitionContext from '../context/TransitionContext';

// Composant pour gérer les animations de transition entre les pages
const TransitionComponent = ({ children }) => {
  const location = useLocation();
  const { toggleCompleted } = useContext(TransitionContext);

  return (
    <SwitchTransition>
      <Transition
        key={location.pathname} // relance l'animation à chaque changement de route
        timeout={500}
        onEnter={(node) => {
          toggleCompleted(false); // transition commencée
          gsap.set(node, { autoAlpha: 0, scale: 0.8, xPercent: -100 }); // état initial
          gsap
            .timeline({
              paused: true,
              onComplete: () => toggleCompleted(true), // transition terminée
            })
            .to(node, { autoAlpha: 1, xPercent: 0, duration: 0.25 }) // apparition + slide
            .to(node, { scale: 1, duration: 0.25 }) // scale normal
            .play();
        }}
        onExit={(node) => {
          gsap
            .timeline({ paused: true })
            .to(node, { scale: 0.8, duration: 0.2 }) // réduction de taille
            .to(node, { xPercent: 100, autoAlpha: 0, duration: 0.2 }) // slide + disparition
            .play();
        }}
      >
        {children}
      </Transition>
    </SwitchTransition>
  );
};

export default TransitionComponent;
