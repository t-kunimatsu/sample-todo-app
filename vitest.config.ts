import { defineConfig } from "vitest/config";
import react from "@vitejs/plugin-react";
import { resolve } from "path";

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      "@": resolve(__dirname, "resources/js"),
    },
  },
  test: {
    globals: true,
    environment: "jsdom",
    setupFiles: "./resources/js/vitest.setup.ts",
  },
});
