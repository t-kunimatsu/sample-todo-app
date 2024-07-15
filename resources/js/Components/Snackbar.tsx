import Alert from "@mui/material/Alert";
import { Box, Snackbar as MuiSnackbar, Slide, Typography } from "@mui/material";
import { useTodoBoard } from "./useTodoBoard";
import { TransitionProps } from "@mui/material/transitions";

function SlideTransition(props: TransitionProps & { children: React.ReactElement }) {
  return <Slide {...props} direction="down" />;
}

export const Snackbar: React.FC = () => {
  const snackbarMessages = useTodoBoard((state) => state.snackbarMessages);
  const snackbarOpen = useTodoBoard((state) => state.snackbarOpen);
  const setSnackbarOpen = useTodoBoard((state) => state.setSnackbarOpen);
  return (
    <Box sx={{ width: "500px" }}>
      <MuiSnackbar
        anchorOrigin={{ vertical: "top", horizontal: "right" }}
        open={snackbarOpen}
        onClose={() => setSnackbarOpen(false)}
        TransitionComponent={SlideTransition}
        autoHideDuration={3000}
      >
        <Alert severity="error" variant="filled" sx={{ width: "100%" }}>
          {snackbarMessages.map((message, index) => (
            <Typography key={index} component="span" variant="body2" display="block">
              {message}
            </Typography>
          ))}
        </Alert>
      </MuiSnackbar>
    </Box>
  );
};
