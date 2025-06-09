import React from 'react';
import { Button } from './Button'

export function CallToAction() {
  return (
    <section className="bg-gradient-to-br from-[#0062FF] via-[#0052CC] to-[#0041AA] text-white py-16 px-6 mb-2">
      <div className="max-w-4xl mx-auto text-center">
        {/* Titre principal */}
        <h2 className="text-3xl md:text-5xl font-bold mb-4">
          Prêt à commencer votre parcours ?
        </h2>
        
        {/* Description */}
        <p className="text-lg md:text-xl text-white/90 mb-8 max-w-2xl mx-auto">
          Rejoignez des milliers d'étudiants qui ont choisi l'excellence académique. 
          Découvrez nos formations et trouvez celle qui vous correspond.
        </p>
        
        {/* Boutons */}
        <div className='flex flex-col sm:flex-row gap-4 justify-center items-center mb-12'>
          <Button
            to="/register"
            label="S'inscrire maintenant"
            className="bg-white text-[#0062FF] px-8 py-3 rounded-lg font-semibold hover:border-2 border-white hover:bg-gray-50 transition-colors duration-200 w-full sm:w-auto"
          />
          <Button
            to="/events"
            label="Découvrir nos formations"
            className="text-[#0062FF]  px-8 py-3 rounded-lg font-semibold hover:border-2 border-white hover:bg-white hover:text-[#0062FF] transition-colors duration-200 w-full sm:w-auto"
          />
        </div>
        
        {/* Informations supplémentaires */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 pt-8 border-t border-white/20">
          <div className="text-center">
            <div className="text-2xl font-bold mb-2">3500+</div>
            <div className="text-white/80 text-sm">Étudiants diplômés</div>
          </div>
          
          <div className="text-center">
            <div className="text-2xl font-bold mb-2">95%</div>
            <div className="text-white/80 text-sm">Taux d'insertion</div>
          </div>
          
          <div className="text-center">
            <div className="text-2xl font-bold mb-2">5+</div>
            <div className="text-white/80 text-sm">Formations disponibles</div>
          </div>
        </div>
        
        {/* Contact info */}
        <div className="mt-8 text-sm text-white/70">
          <p>Des questions ? Contactez-nous au <span className="font-semibold">04 84 89 43 69</span></p>
        </div>
      </div>
    </section>
  );
}