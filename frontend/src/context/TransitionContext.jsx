import React, { createContext, useState } from 'react';

// Création du contexte pour gérer l'état de transition
const TransitionContext = createContext({ completed: false });

// Provider qui enveloppe l'app et fournit l'état et la fonction pour changer 'completed'
export const TransitionProvider = ({ children }) => {
  const [completed, setCompleted] = useState(false);

  // Fonction pour modifier l'état 'completed'
  const toggleCompleted = (value) => {
    setCompleted(value);
  };

  return (
    <TransitionContext.Provider
      value={{
        toggleCompleted,
        completed,
      }}
    >
      {children}
    </TransitionContext.Provider>
  );
};

export default TransitionContext;
