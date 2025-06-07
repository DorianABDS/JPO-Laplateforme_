// Page affichée pour les routes non définies (error 404)
export function NotFound() {
  return (
    <div className="p-8 text-center">
      <h1 className="text-3xl font-bold text[#0062FF]">404 - Page non trouvée</h1>
      <p className="mt-4">La page que vous cherchez n'existe pas.</p>
    </div>
  );
}
