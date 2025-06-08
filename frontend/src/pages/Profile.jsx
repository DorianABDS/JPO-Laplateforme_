import React, { useState } from 'react';
import { User, Mail, Edit3, Save, X } from 'lucide-react'; // Icônes UI

export function Profile() {
  // État principal de l'utilisateur
  const [isEditing, setIsEditing] = useState(false);
  const [userInfo, setUserInfo] = useState({
    prenom: '',
    nom: '',
    email: ''
  });

  // Formulaire temporaire pour l'édition
  const [editForm, setEditForm] = useState(userInfo);

  // Active le mode édition
  const handleEdit = () => {
    setIsEditing(true);
    setEditForm(userInfo);
  };

  // Enregistre les modifications
  const handleSave = () => {
    setUserInfo(editForm);
    setIsEditing(false);
  };

  // Annule l'édition
  const handleCancel = () => {
    setEditForm(userInfo);
    setIsEditing(false);
  };

  // Met à jour un champ du formulaire
  const handleInputChange = (field, value) => {
    setEditForm(prev => ({ ...prev, [field]: value }));
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-8 px-4">
      <div className="max-w-2xl mx-auto">

        {/* Titre de la page */}
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-[#0062FF] mb-2">Mon Profil</h1>
          <p className="text-gray-600">Journée Porte Ouverte</p>
        </div>

        {/* Carte de profil */}
        <div className="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">

          {/* En-tête de la carte */}
          <div className="bg-gradient-to-br from-[#0062FF] via-[#0052CC] to-[#0041AA] px-8 py-6">
            <div className="flex flex-col items-center justify-center md:flex-row md:justify-between gap-5">
              <div className="flex items-center space-x-4">
                <div className="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                  <User className="w-8 h-8 text-[#0062FF]" />
                </div>
                <div className="text-white">
                  <h2 className="text-xl font-bold">
                    {userInfo.prenom} {userInfo.nom}
                  </h2>
                  <p className="text-blue-100 text-sm">Participant</p>
                </div>
              </div>

              {/* Bouton "Modifier" si pas en édition */}
              {!isEditing && (
                <button
                  onClick={handleEdit}
                  className="bg-white text-[#0062FF] px-4 py-2 rounded-full transition-all duration-200 flex items-center space-x-2 hover:-translate-y-1"
                >
                  <Edit3 className="w-4 h-4" />
                  <span>Modifier</span>
                </button>
              )}
            </div>
          </div>

          {/* Contenu de la carte */}
          <div className="p-8">
            {!isEditing ? (
              // Mode affichage
              <div className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  {/* Prénom */}
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-500 uppercase tracking-wide">Prénom</label>
                    <div className="bg-gray-100 rounded-lg p-4 border-l-8 border-[#0062FF]">
                      <p className="text-gray-800 font-medium text-lg">{userInfo.prenom}</p>
                    </div>
                  </div>

                  {/* Nom */}
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-500 uppercase tracking-wide">Nom</label>
                    <div className="bg-gray-100 rounded-lg p-4 border-l-8 border-[#0062FF]">
                      <p className="text-gray-800 font-medium text-lg">{userInfo.nom}</p>
                    </div>
                  </div>
                </div>

                {/* Email */}
                <div className="space-y-2">
                  <label className="text-sm font-medium text-gray-500 uppercase tracking-wide">Email</label>
                  <div className="bg-gray-100 rounded-lg p-4 border-l-8 border-[#0062FF] flex items-center space-x-3">
                    <Mail className="w-5 h-5 text-[#0041AA]" />
                    <p className="text-gray-800 font-medium text-lg">{userInfo.email}</p>
                  </div>
                </div>
              </div>
            ) : (
              // Mode édition
              <div className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  {/* Input prénom */}
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">Prénom</label>
                    <input
                      type="text"
                      value={editForm.prenom}
                      onChange={(e) => handleInputChange('prenom', e.target.value)}
                      className="w-full px-4 py-3 outline-none border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0062FF] focus:border-transparent transition-all duration-200"
                      placeholder="Votre prénom"
                    />
                  </div>

                  {/* Input nom */}
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">Nom</label>
                    <input
                      type="text"
                      value={editForm.nom}
                      onChange={(e) => handleInputChange('nom', e.target.value)}
                      className="w-full px-4 py-3 outline-none border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0062FF] focus:border-transparent transition-all duration-200"
                      placeholder="Votre nom"
                    />
                  </div>
                </div>

                {/* Input email */}
                <div className="space-y-2">
                  <label className="text-sm font-medium text-gray-700">Email</label>
                  <input
                    type="email"
                    value={editForm.email}
                    onChange={(e) => handleInputChange('email', e.target.value)}
                    className="w-full px-4 py-3 outline-none border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0062FF] focus:border-transparent transition-all duration-200"
                    placeholder="votre.email@example.com"
                  />
                </div>

                {/* Boutons sauvegarder / annuler */}
                <div className="flex flex-col items-center justify-center w-full pt-4 space-y-3 md:flex-row md:space-x-3 md:space-y-0">
                  <button
                    onClick={handleSave}
                    className="w-full md:flex-1 bg-gradient-to-br from-[#0062FF] via-[#0052CC] to-[#0041AA] text-white px-6 py-3 shadow rounded-full transition-all duration-200 flex items-center justify-center space-x-2 font-medium hover:-translate-y-1"
                  >
                    <Save className="w-4 h-4" />
                    <span>Sauvegarder</span>
                  </button>

                  <button
                    onClick={handleCancel}
                    className="w-full md:flex-1 bg-white text-[#0062FF] px-6 py-3 shadow rounded-full border border-[#0062FF] hover:bg-gray-200 duration-200 flex items-center justify-center space-x-2 font-medium hover:text-white hover:bg-gradient-to-br 
                      hover:from-[#0062FF] hover:via-[#0052CC] hover:to-[#0041AA] 
                      hover:shadow-lg hover:-translate-y-1"
                  >
                    <X className="w-4 h-4" />
                    <span>Annuler</span>
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Note informative */}
        <div className="mt-6 text-center text-gray-500 text-sm">
          <p>Ces informations sont utilisées pour votre participation à la journée porte ouverte</p>
        </div>
      </div>
    </div>
  );
}
